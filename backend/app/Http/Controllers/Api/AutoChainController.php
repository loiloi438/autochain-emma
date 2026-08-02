<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Assignment;
use App\Models\BlockchainTx;
use App\Models\FuelLog;
use App\Models\MaintenanceLog;
use App\Models\MileageLog;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Services\BlockchainIndexer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use kornrunner\Eth;
use kornrunner\Keccak;

class AutoChainController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        if ($request->filled('wallet_address') && $request->filled('message') && $request->filled('signature')) {
            $data = $request->validate([
                'wallet_address' => ['required', 'string', 'max:128'],
                'message' => ['required', 'string'],
                'signature' => ['required', 'string'],
            ]);

            $wallet = strtolower($data['wallet_address']);

            try {
                $recovered = $this->recoverAddressFromSignature($data['message'], $data['signature']);
            } catch (\Throwable $exception) {
                throw ValidationException::withMessages(['signature' => 'La signature MetaMask est invalide.']);
            }

            if ($recovered !== $wallet) {
                throw ValidationException::withMessages(['wallet_address' => 'La signature MetaMask est invalide.']);
            }

            $user = User::whereRaw('LOWER(wallet_address) = ?', [$wallet])->first();
            if (! $user) {
                throw ValidationException::withMessages(['wallet_address' => 'Ce wallet n’est associé à aucun compte.']);
            }

            return response()->json([
                'token' => $user->createToken('autochain')->plainTextToken,
                'user' => $user->load('roles'),
            ]);
        }

        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required']]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'Identifiants invalides.']);
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();

        return response()->json([
            'token' => $user->createToken('autochain')->plainTextToken,
            'user' => $user->load('roles'),
        ])->header('Content-Type', 'application/json');
    }

    private function recoverAddressFromSignature(string $message, string $signature): string
    {
        if (str_starts_with($signature, '0x')) {
            $signature = substr($signature, 2);
        }

        if (strlen($signature) !== 130) {
            throw new \RuntimeException('Signature invalide.');
        }

        $r = '0x' . substr($signature, 0, 64);
        $s = '0x' . substr($signature, 64, 64);
        $v = hexdec(substr($signature, 128, 2));

        if ($v >= 27) {
            $v -= 27;
        }

        $hashed = Eth::hashPersonalMessage(bin2hex($message));
        $publicKey = Eth::ecRecover($hashed, $r, $s, $v);

        if (str_starts_with($publicKey, '0x')) {
            $publicKey = substr($publicKey, 2);
        }

        if (str_starts_with($publicKey, '04')) {
            $publicKey = substr($publicKey, 2);
        }

        $address = substr(Keccak::hash(hex2bin($publicKey), 256), 24);

        return '0x' . strtolower($address);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'wallet_address' => $user->wallet_address,
            'roles' => $user->roles->map(fn ($role) => ['name' => $role->name]),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function linkWallet(Request $request): JsonResponse
    {
        $data = $request->validate(['wallet_address' => ['required', 'string', 'max:128']]);
        $request->user()->update(['wallet_address' => strtolower($data['wallet_address'])]);

        $user = $request->user()->fresh()->load('roles');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'wallet_address' => $user->wallet_address,
            'roles' => $user->roles->map(fn ($role) => ['name' => $role->name]),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function dashboard(): JsonResponse
    {
        return response()->json([
            'fleet' => [
                'total' => Vehicle::count(),
                'available' => Vehicle::where('status', 'available')->count(),
                'assigned' => Vehicle::where('status', 'assigned')->count(),
                'maintenance' => Vehicle::whereIn('status', ['maintenance', 'broken'])->count(),
            ],
            'alerts' => Alert::with('vehicle')->where('status', 'open')->latest()->limit(10)->get(),
            'recent_timeline' => $this->timelineItems(null, 8),
        ]);
    }

    public function vehicles(): JsonResponse
    {
        return response()->json(Vehicle::with(['assignments.driver', 'alerts'])->latest()->paginate(20));
    }

    public function storeVehicle(Request $request): JsonResponse
    {
        if (! $this->canManageFleet($request->user())) {
            return $this->forbidden('Vous n’êtes pas autorisé à créer un véhicule.');
        }
        $data = $request->validate([
            'plate_number' => ['required', 'string', 'max:32', 'unique:vehicles'],
            'vin' => ['nullable', 'string', 'max:64'],
            'brand' => ['required', 'string', 'max:120'],
            'model' => ['required', 'string', 'max:120'],
            'year' => ['nullable', 'integer', 'min:1980', 'max:2100'],
            'current_km' => ['nullable', 'integer', 'min:0'],
            'technical_inspection_due_at' => ['nullable', 'date'],
            'insurance_due_at' => ['nullable', 'date'],
            'oil_change_due_km' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['current_km'] ??= 0;
        $data['vin_hash'] = '0x'.hash('sha256', ($data['vin'] ?? $data['plate_number']));

        return response()->json(Vehicle::create($data), 201);
    }

    public function showVehicle(Vehicle $vehicle): JsonResponse
    {
        return response()->json($vehicle->load(['assignments.driver', 'mileageLogs.user', 'maintenanceLogs.garage', 'documents', 'alerts']));
    }

    public function updateVehicle(Request $request, Vehicle $vehicle): JsonResponse
    {
        if (! $this->canManageFleet($request->user())) {
            return $this->forbidden('Vous n’êtes pas autorisé à modifier ce véhicule.');
        }

        $vehicle->update($request->validate([
            'brand' => ['sometimes', 'string', 'max:120'],
            'model' => ['sometimes', 'string', 'max:120'],
            'status' => ['sometimes', 'in:available,assigned,maintenance,broken,archived'],
            'technical_inspection_due_at' => ['nullable', 'date'],
            'insurance_due_at' => ['nullable', 'date'],
            'oil_change_due_km' => ['nullable', 'integer', 'min:0'],
        ]));

        return response()->json($vehicle->fresh());
    }

    public function assignVehicle(Request $request, Vehicle $vehicle): JsonResponse
    {
        if (! $this->canManageFleet($request->user())) {
            return $this->forbidden('Vous n’êtes pas autorisé à affecter un véhicule.');
        }

        $data = $request->validate(['user_id' => ['required', 'exists:users,id'], 'notes' => ['nullable', 'string']]);
        Assignment::where('vehicle_id', $vehicle->id)->where('status', 'active')->update(['status' => 'closed', 'ends_at' => now()]);
        $assignment = Assignment::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $data['user_id'],
            'assigned_by' => $request->user()->id,
            'notes' => $data['notes'] ?? null,
        ]);
        $vehicle->update(['status' => 'assigned']);

        return response()->json($assignment->load(['driver', 'vehicle']), 201);
    }

    public function recordMileage(Request $request, Vehicle $vehicle): JsonResponse
    {
        if (! $this->canDriveFleet($request->user())) {
            return $this->forbidden('Vous n’êtes pas autorisé à enregistrer un kilométrage.');
        }

        $data = $request->validate(['km' => ['required', 'integer', 'min:0'], 'tx_hash' => ['nullable', 'string'], 'note' => ['nullable', 'string']]);
        if ($data['km'] < $vehicle->current_km) {
            return response()->json(['message' => 'Le kilométrage ne peut pas diminuer.'], 422);
        }

        $log = MileageLog::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $request->user()->id,
            'km' => $data['km'],
            'source' => empty($data['tx_hash']) ? 'backend' : 'blockchain',
            'tx_hash' => $data['tx_hash'] ?? null,
            'note' => $data['note'] ?? null,
        ]);
        $vehicle->update(['current_km' => $data['km']]);

        return response()->json($log, 201);
    }

    public function recordMaintenance(Request $request, Vehicle $vehicle): JsonResponse
    {
        if (! $this->canMaintainFleet($request->user())) {
            return $this->forbidden('Vous n’êtes pas autorisé à enregistrer une maintenance.');
        }

        $data = $request->validate([
            'service_type' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'parts' => ['nullable', 'array'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'tx_hash' => ['nullable', 'string'],
        ]);
        $data['parts_hash'] = isset($data['parts']) ? '0x'.hash('sha256', json_encode($data['parts'])) : null;
        $data['vehicle_id'] = $vehicle->id;
        $data['garage_id'] = $request->user()->id;

        return response()->json(MaintenanceLog::create($data), 201);
    }

    public function uploadDocument(Request $request, Vehicle $vehicle): JsonResponse
    {
        if (! $this->canManageFleet($request->user())) {
            return $this->forbidden('Vous n’êtes pas autorisé à téléverser un document.');
        }

        $data = $request->validate([
            'document' => ['required', 'file', 'max:10240'],
            'document_type' => ['required', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:160'],
            'expires_at' => ['nullable', 'date'],
            'is_public' => ['boolean'],
            'tx_hash' => ['nullable', 'string'],
        ]);
        $data['is_public'] = $data['is_public'] ?? false;
        $file = $request->file('document');
        $path = $file->store("vehicles/{$vehicle->id}", 'local');
        $hash = hash_file('sha256', storage_path('app/'.$path));

        $ipfsCid = null;
        if ($data['is_public'] && config('services.ipfs.enabled')) {
            try {
                $ipfsApi = rtrim(config('services.ipfs.api_url'), '/');
                $response = Http::withOptions(['verify' => false])
                    ->attach('file', fopen(storage_path('app/'.$path), 'r'), basename($path))
                    ->post($ipfsApi . '/api/v0/add?pin=true');

                if ($response->successful()) {
                    $json = $response->json();
                    $ipfsCid = $json['Hash'] ?? (is_array($json) && isset($json[0]['Hash']) ? $json[0]['Hash'] : null);
                }
            } catch (\Exception $e) {
                $ipfsCid = null;
            }
        }

        $doc = VehicleDocument::create([
            'vehicle_id' => $vehicle->id,
            'uploaded_by' => $request->user()->id,
            'document_type' => $data['document_type'],
            'title' => $data['title'],
            'path' => $path,
            'sha256_hash' => $hash,
            'ipfs_cid' => $ipfsCid ?? ($data['is_public'] ? 'ipfs-sim-'.substr($hash, 0, 24) : null),
            'tx_hash' => $data['tx_hash'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'is_public' => $data['is_public'],
        ]);

        return response()->json($doc, 201);
    }

    public function alerts(): JsonResponse
    {
        return response()->json(Alert::with('vehicle')->where('status', 'open')->orderBy('due_at')->get());
    }

    public function generateAlerts(): JsonResponse
    {
        Vehicle::query()->each(function (Vehicle $vehicle) {
            if ($vehicle->technical_inspection_due_at && $vehicle->technical_inspection_due_at->diffInDays(now(), false) >= -30) {
                Alert::firstOrCreate(['vehicle_id' => $vehicle->id, 'type' => 'technical_inspection', 'status' => 'open'], ['title' => 'Contrôle technique à prévoir', 'due_at' => $vehicle->technical_inspection_due_at]);
            }
            if ($vehicle->insurance_due_at && $vehicle->insurance_due_at->diffInDays(now(), false) >= -30) {
                Alert::firstOrCreate(['vehicle_id' => $vehicle->id, 'type' => 'insurance', 'status' => 'open'], ['title' => 'Assurance à renouveler', 'due_at' => $vehicle->insurance_due_at]);
            }
            if ($vehicle->oil_change_due_km && $vehicle->current_km >= ($vehicle->oil_change_due_km - 500)) {
                Alert::firstOrCreate(['vehicle_id' => $vehicle->id, 'type' => 'oil_change', 'status' => 'open'], ['title' => 'Vidange proche', 'message' => 'Seuil kilométrique bientôt atteint.']);
            }
        });

        return $this->alerts();
    }

    public function storeFuel(Request $request, Vehicle $vehicle): JsonResponse
    {
        if (! $this->canDriveFleet($request->user())) {
            return $this->forbidden('Vous n’êtes pas autorisé à enregistrer un carburant.');
        }

        $data = $request->validate(['liters' => ['required', 'numeric', 'min:0.1'], 'amount' => ['nullable', 'numeric', 'min:0'], 'km' => ['required', 'integer', 'min:0']]);
        $data['vehicle_id'] = $vehicle->id;
        $data['user_id'] = $request->user()->id;

        return response()->json(FuelLog::create($data), 201);
    }

    public function syncTx(Request $request, BlockchainIndexer $indexer): JsonResponse
    {
        // Allow any authenticated user to submit blockchain tx payloads.
        $data = $request->validate([
            'tx_hash' => ['required', 'string'],
            'action' => ['required', 'string'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'status' => ['nullable', 'in:pending,confirmed,failed'],
            'payload' => ['nullable', 'array'],
        ]);
        $data['user_id'] = $request->user()->id;
        $data['status'] ??= 'pending';

        $tx = $indexer->syncAndApply($data);
        return response()->json($tx, 201);
    }

    public function adminUsers(): JsonResponse
    {
        $users = User::with('roles')->get()->map(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'wallet_address' => $user->wallet_address,
                'roles' => $user->roles->pluck('name'),
            ];
        });

        return response()->json($users);
    }

    public function adminUpdateUserRoles(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['string'],
        ]);

        $roles = array_map('strtolower', $data['roles']);

        DB::transaction(function () use ($user, $roles) {
            foreach ($roles as $roleName) {
                SpatieRole::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            }
            $user->syncRoles($roles);
        });

        return response()->json([
            'updated' => true,
            'roles' => $user->roles->pluck('name'),
        ]);
    }

    public function adminSaveContractConfig(Request $request): JsonResponse
    {
        $data = $request->validate([
            'address' => ['required', 'string'],
            'network' => ['required', 'string'],
        ]);

        Storage::disk('local')->put('contract-config.json', json_encode([
            'address' => $data['address'],
            'network' => $data['network'],
        ], JSON_PRETTY_PRINT));

        return response()->json(['saved' => true]);
    }

    public function managerFleet(): JsonResponse
    {
        $vehicles = Vehicle::with(['assignments.driver'])->get()->map(function (Vehicle $vehicle) {
            return [
                'id' => $vehicle->id,
                'plate_number' => $vehicle->plate_number,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'status' => $vehicle->status,
                'current_km' => $vehicle->current_km,
                'driver' => $vehicle->assignments->first()?->driver?->only(['id', 'name']) ?? null,
            ];
        });

        return response()->json($vehicles);
    }

    public function managerFuelSummary(): JsonResponse
    {
        $summary = FuelLog::selectRaw('vehicle_id, SUM(liters) AS total_liters, SUM(amount) AS total_amount')
            ->groupBy('vehicle_id')
            ->get();

        return response()->json($summary);
    }

    public function driverAssignments(Request $request): JsonResponse
    {
        $assignments = Assignment::with('vehicle')
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->get();

        return response()->json(['current' => $assignments->first(), 'all' => $assignments]);
    }

    public function driverCheckin(Request $request, Vehicle $vehicle): JsonResponse
    {
        if (! $this->canDriveFleet($request->user())) {
            return $this->forbidden('Vous n’êtes pas autorisé à enregistrer la prise en charge.');
        }

        $vehicle->update(['status' => 'assigned']);

        return response()->json(['checked_in' => true, 'vehicle' => $vehicle]);
    }

    public function garageMaintenances(): JsonResponse
    {
        $items = MaintenanceLog::with('vehicle')->latest('performed_at')->limit(20)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'vehicle_id' => $item->vehicle_id,
                'vehicle_plate' => $item->vehicle?->plate_number,
                'service_type' => $item->service_type,
                'description' => $item->description,
                'parts' => $item->parts,
                'performed_at' => $item->performed_at,
                'tx_hash' => $item->tx_hash,
                'status' => $item->tx_hash ? BlockchainTx::where('tx_hash', $item->tx_hash)->value('status') : 'backend',
            ];
        });

        return response()->json($items);
    }

    public function contractInfo(BlockchainIndexer $indexer): JsonResponse
    {
        $config = [];
        if (Storage::disk('local')->exists('contract-config.json')) {
            $config = json_decode(Storage::disk('local')->get('contract-config.json'), true) ?? [];
        }

        return response()->json([
            'address' => $indexer->contractAddress(),
            'artifact' => $indexer->artifact(),
            'config' => $config,
        ]);
    }

    public function syncRoles(BlockchainIndexer $indexer): JsonResponse
    {
        $roles = $indexer->roles();
        $mapped = [];

        DB::transaction(function () use ($roles, &$mapped) {
            foreach ($roles as $roleName => $address) {
                $roleNameNorm = strtolower($roleName);
                $role = SpatieRole::firstOrCreate(['name' => $roleNameNorm, 'guard_name' => 'web']);

                $user = User::where('wallet_address', strtolower($address))->first();
                if ($user) {
                    $user->assignRole($role);
                    $mapped[$roleNameNorm] = $user->id;
                } else {
                    $mapped[$roleNameNorm] = null;
                }
            }
        });

        return response()->json(['synced' => true, 'mapping' => $mapped]);
    }

    public function timeline(?Vehicle $vehicle = null): JsonResponse
    {
        return response()->json($this->timelineItems($vehicle));
    }

    private function canManageFleet(User $user): bool
    {
        return $user->hasRole('manager') || $user->hasRole('admin');
    }

    private function canDriveFleet(User $user): bool
    {
        return $user->hasRole('driver') || $this->canManageFleet($user);
    }

    private function canMaintainFleet(User $user): bool
    {
        return $user->hasRole('garage') || $user->hasRole('admin');
    }

    private function forbidden(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 403);
    }

    public function publicHistory(Vehicle $vehicle): JsonResponse
    {
        $timeline = $this->timelineItems($vehicle);

        return response()->json([
            'vehicle' => $vehicle->only(['id', 'plate_number', 'vin_hash', 'brand', 'model', 'current_km', 'status']),
            'certified' => $timeline->where('certified', true)->values(),
            'pending' => $timeline->where('certified', false)->values(),
            'summary' => [
                'total' => $timeline->count(),
                'certified' => $timeline->where('certified', true)->count(),
                'pending' => $timeline->where('certified', false)->count(),
            ],
        ]);
    }

    private function timelineItems(?Vehicle $vehicle = null, int $limit = 50)
    {
        $vehicleId = $vehicle?->id;
        $mileages = MileageLog::with('user', 'vehicle')->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))->latest('recorded_at')->limit($limit)->get()->map(function ($item) {
            $tx = $item->tx_hash ? BlockchainTx::where('tx_hash', $item->tx_hash)->first() : null;
            $certified = $tx?->status === 'confirmed';

            return [
                'type' => 'mileage',
                'label' => 'Relevé kilométrique',
                'date' => $item->recorded_at,
                'vehicle' => $item->vehicle?->plate_number,
                'summary' => $item->km.' km',
                'certified' => $certified,
                'tx_hash' => $item->tx_hash,
                'status' => $tx?->status ?? 'backend',
            ];
        });
        $maintenances = MaintenanceLog::with('garage', 'vehicle')->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))->latest('performed_at')->limit($limit)->get()->map(function ($item) {
            $tx = $item->tx_hash ? BlockchainTx::where('tx_hash', $item->tx_hash)->first() : null;
            $certified = $tx?->status === 'confirmed';

            return [
                'type' => 'maintenance',
                'label' => $item->service_type,
                'date' => $item->performed_at,
                'vehicle' => $item->vehicle?->plate_number,
                'summary' => $item->description,
                'certified' => $certified,
                'tx_hash' => $item->tx_hash,
                'status' => $tx?->status ?? 'backend',
            ];
        });
        $documents = VehicleDocument::with('vehicle')->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))->latest()->limit($limit)->get()->map(function ($item) {
            $tx = $item->tx_hash ? BlockchainTx::where('tx_hash', $item->tx_hash)->first() : null;
            $certified = $tx?->status === 'confirmed';

            return [
                'type' => 'document',
                'label' => $item->title,
                'date' => $item->created_at,
                'vehicle' => $item->vehicle?->plate_number,
                'summary' => $item->document_type.' - SHA256 '.$item->sha256_hash,
                'certified' => $certified,
                'tx_hash' => $item->tx_hash,
                'status' => $tx?->status ?? 'backend',
            ];
        });

        return $mileages->concat($maintenances)->concat($documents)->sortByDesc('date')->take($limit)->values();
    }
}
