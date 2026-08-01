// SPDX-License-Identifier: MIT
pragma solidity ^0.8.24;

/**
 * @title VehicleRegistry
 * @notice Stocke uniquement des preuves techniques (hashes), kilometrage et statuts critiques.
 *         Aucune donnee nominative (RGPD).
 */
contract VehicleRegistry {
    enum Role {
        None,
        Admin,
        Manager,
        Driver,
        Garage,
        Auditor
    }

    struct Vehicle {
        bytes32 vinHash;
        uint256 currentKm;
        bool exists;
        uint8 status; // 0 available, 1 assigned, 2 maintenance, 3 broken, 4 archived
    }

    address public owner;
    uint256 public nextVehicleId = 1;

    mapping(address => Role) public roles;
    mapping(uint256 => Vehicle) public vehicles;
    mapping(bytes32 => uint256) public vinHashToVehicleId;

    event RoleGranted(address indexed account, Role role);
    event VehicleRegistered(uint256 indexed vehicleId, bytes32 vinHash, uint256 initialKm, address indexed actor);
    event MileageRecorded(uint256 indexed vehicleId, uint256 km, address indexed actor, uint256 timestamp);
    event MaintenanceRecorded(
        uint256 indexed vehicleId,
        string serviceType,
        bytes32 partsHash,
        address indexed garage,
        uint256 timestamp
    );
    event DocumentHashRecorded(
        uint256 indexed vehicleId,
        bytes32 docHash,
        string docType,
        address indexed actor,
        uint256 timestamp
    );
    event StatusUpdated(uint256 indexed vehicleId, uint8 status, address indexed actor);

    modifier onlyOwner() {
        require(msg.sender == owner, "Not owner");
        _;
    }

    modifier onlyRole(Role requiredRole) {
        require(roles[msg.sender] == requiredRole || roles[msg.sender] == Role.Admin, "Unauthorized role");
        _;
    }

    modifier onlyManagerOrAdmin() {
        Role role = roles[msg.sender];
        require(role == Role.Manager || role == Role.Admin, "Manager/Admin only");
        _;
    }

    modifier onlyDriverManagerOrAdmin() {
        Role role = roles[msg.sender];
        require(role == Role.Driver || role == Role.Manager || role == Role.Admin, "Driver/Manager/Admin only");
        _;
    }

    modifier vehicleExists(uint256 vehicleId) {
        require(vehicles[vehicleId].exists, "Unknown vehicle");
        _;
    }

    constructor() {
        owner = msg.sender;
        roles[msg.sender] = Role.Admin;
        emit RoleGranted(msg.sender, Role.Admin);
    }

    function grantRole(address account, Role role) external onlyOwner {
        roles[account] = role;
        emit RoleGranted(account, role);
    }

    function registerVehicle(bytes32 vinHash, uint256 initialKm)
        external
        onlyManagerOrAdmin
        returns (uint256 vehicleId)
    {
        require(vinHash != bytes32(0), "Invalid vinHash");
        require(vinHashToVehicleId[vinHash] == 0, "VIN already registered");

        vehicleId = nextVehicleId++;
        vehicles[vehicleId] = Vehicle({vinHash: vinHash, currentKm: initialKm, exists: true, status: 0});
        vinHashToVehicleId[vinHash] = vehicleId;

        emit VehicleRegistered(vehicleId, vinHash, initialKm, msg.sender);
        emit MileageRecorded(vehicleId, initialKm, msg.sender, block.timestamp);
    }

    function recordMileage(uint256 vehicleId, uint256 km)
        external
        onlyDriverManagerOrAdmin
        vehicleExists(vehicleId)
    {
        require(km > vehicles[vehicleId].currentKm, "Km must increase");
        vehicles[vehicleId].currentKm = km;
        emit MileageRecorded(vehicleId, km, msg.sender, block.timestamp);
    }

    function recordMaintenance(uint256 vehicleId, string calldata serviceType, bytes32 partsHash)
        external
        onlyRole(Role.Garage)
        vehicleExists(vehicleId)
    {
        require(bytes(serviceType).length > 0, "Empty service type");
        emit MaintenanceRecorded(vehicleId, serviceType, partsHash, msg.sender, block.timestamp);
    }

    function recordDocumentHash(uint256 vehicleId, bytes32 docHash, string calldata docType)
        external
        onlyManagerOrAdmin
        vehicleExists(vehicleId)
    {
        require(docHash != bytes32(0), "Invalid doc hash");
        emit DocumentHashRecorded(vehicleId, docHash, docType, msg.sender, block.timestamp);
    }

    function updateStatus(uint256 vehicleId, uint8 status) external onlyManagerOrAdmin vehicleExists(vehicleId) {
        require(status <= 4, "Invalid status");
        vehicles[vehicleId].status = status;
        emit StatusUpdated(vehicleId, status, msg.sender);
    }

    function getVehicle(uint256 vehicleId)
        external
        view
        returns (bytes32 vinHash, uint256 currentKm, bool exists, uint8 status)
    {
        Vehicle memory vehicle = vehicles[vehicleId];
        return (vehicle.vinHash, vehicle.currentKm, vehicle.exists, vehicle.status);
    }
}
