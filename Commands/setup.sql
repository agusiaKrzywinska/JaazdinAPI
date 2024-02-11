-- Create the boats Table
CREATE TABLE boats (
    boatName varchar(40),
    city varchar(40),
    country varchar(40),
    waitTime int,
    timeInTown int,
    jobsAffected varchar(255),
    tier2Ability varchar(255),
    tableToGenerate varchar(40),
    isRunning boolean,
    isTier2 boolean,
    weeksLeft int,
    isInTown boolean,
    PRIMARY KEY (boatName)
);

-- Add new boat
INSERT INTO `boats` (`boatName`, `city`, `country`, `waitTime`, `timeInTown`, `jobsAffected`, `tier2Ability`, `tableToGenerate`, `isTier2`, `weeksLeft`, `isInTown`, `isRunning`) 
VALUES("The Gilded Counch","Coarsil","Ociaria",8,2,"Gambling Performance","When a character uses a bardic in downtime, they add an addition +1 to the roll. Character with proficiency in Disguise kits can increase their Wage dice for Performing to d12s while the fleet is in town.","",false,8,false,false);