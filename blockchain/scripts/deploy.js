const fs = require("fs");
const path = require("path");
const hre = require("hardhat");

async function main() {
  const [deployer, manager, driver, garage, auditor] = await hre.ethers.getSigners();
  const VehicleRegistry = await hre.ethers.getContractFactory("VehicleRegistry");
  const registry = await VehicleRegistry.deploy();
  await registry.waitForDeployment();

  const address = await registry.getAddress();
  await (await registry.grantRole(manager.address, 2)).wait();
  await (await registry.grantRole(driver.address, 3)).wait();
  await (await registry.grantRole(garage.address, 4)).wait();
  await (await registry.grantRole(auditor.address, 5)).wait();

  const artifact = await hre.artifacts.readArtifact("VehicleRegistry");
  const sharedDir = path.join(__dirname, "..", "shared");
  fs.mkdirSync(sharedDir, { recursive: true });

  const deployment = {
    network: hre.network.name,
    address,
    deployer: deployer.address,
    roles: {
      admin: deployer.address,
      manager: manager.address,
      driver: driver.address,
      garage: garage.address,
      auditor: auditor.address,
    },
    abi: artifact.abi,
  };

  fs.writeFileSync(path.join(sharedDir, "VehicleRegistry.json"), JSON.stringify(deployment, null, 2));

  const frontendAbiDir = path.join(__dirname, "..", "..", "frontend", "src", "abi");
  fs.mkdirSync(frontendAbiDir, { recursive: true });
  fs.writeFileSync(path.join(frontendAbiDir, "VehicleRegistry.json"), JSON.stringify(deployment, null, 2));

  const backendAbiDir = path.join(__dirname, "..", "..", "backend", "storage", "app", "blockchain");
  fs.mkdirSync(backendAbiDir, { recursive: true });
  fs.writeFileSync(path.join(backendAbiDir, "VehicleRegistry.json"), JSON.stringify(deployment, null, 2));

  console.log("VehicleRegistry deployed to:", address);
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
