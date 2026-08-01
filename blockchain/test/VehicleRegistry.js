const { expect } = require("chai");
const { ethers } = require("hardhat");

describe("VehicleRegistry", function () {
  async function deployFixture() {
    const [admin, manager, driver, garage] = await ethers.getSigners();
    const VehicleRegistry = await ethers.getContractFactory("VehicleRegistry");
    const registry = await VehicleRegistry.deploy();
    await registry.waitForDeployment();

    await (await registry.grantRole(manager.address, 2)).wait();
    await (await registry.grantRole(driver.address, 3)).wait();
    await (await registry.grantRole(garage.address, 4)).wait();

    return { registry, admin, manager, driver, garage };
  }

  it("registers a vehicle and stores initial mileage", async function () {
    const { registry, manager } = await deployFixture();
    const vinHash = ethers.id("VF1AUT0CHAIN001");

    await expect(registry.connect(manager).registerVehicle(vinHash, 1000))
      .to.emit(registry, "VehicleRegistered");

    const vehicle = await registry.getVehicle(1);
    expect(vehicle.currentKm).to.equal(1000n);
    expect(vehicle.exists).to.equal(true);
  });

  it("rejects decreasing mileage", async function () {
    const { registry, manager, driver } = await deployFixture();
    const vinHash = ethers.id("VF1AUT0CHAIN002");
    await registry.connect(manager).registerVehicle(vinHash, 5000);

    await expect(registry.connect(driver).recordMileage(1, 4000)).to.be.revertedWith("Km must increase");
  });

  it("records certified maintenance by garage", async function () {
    const { registry, manager, garage } = await deployFixture();
    const vinHash = ethers.id("VF1AUT0CHAIN003");
    await registry.connect(manager).registerVehicle(vinHash, 2000);
    const partsHash = ethers.id("huile-filtre");

    await expect(registry.connect(garage).recordMaintenance(1, "Vidange", partsHash))
      .to.emit(registry, "MaintenanceRecorded");
  });

  it("anchors document hashes without personal data", async function () {
    const { registry, manager } = await deployFixture();
    const vinHash = ethers.id("VF1AUT0CHAIN004");
    await registry.connect(manager).registerVehicle(vinHash, 100);
    const docHash = ethers.id("carte-grise-hash");

    await expect(registry.connect(manager).recordDocumentHash(1, docHash, "carte_grise"))
      .to.emit(registry, "DocumentHashRecorded");
  });
});
