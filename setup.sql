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
--Coarsil
INSERT INTO `boats` (`boatName`, `city`, `country`, `waitTime`, `timeInTown`, `jobsAffected`, `tier2Ability`, `tableToGenerate`, `isTier2`, `weeksLeft`, `isInTown`, `isRunning`) 
VALUES("The Gilded Counch","Coarsil","Ociaria",8,2,"Gambling Performance","When a character uses a bardic in downtime, they add an addition +1 to the roll. Character with proficiency in Disguise kits can increase their Wage dice for Performing to d12s while the fleet is in town.","",false,8,false,false);
--Omicroz
INSERT INTO `boats` (`boatName`, `city`, `country`, `waitTime`, `timeInTown`, `jobsAffected`, `tier2Ability`, `tableToGenerate`, `isTier2`, `weeksLeft`, `isInTown`, `isRunning`) 
VALUES("Freedom's Gambit","Omicroz","Libaria",8,2,"Tinkering Smithing","Characters who make crafting checks using Artisan Tools may also progress an associated profession using the same downtime. Characters making religion checks gain a +2 to their rolls.","",false,8,false,false);
--Harkinal
INSERT INTO `boats` (`boatName`, `city`, `country`, `waitTime`, `timeInTown`, `jobsAffected`, `tier2Ability`, `tableToGenerate`, `isTier2`, `weeksLeft`, `isInTown`, `isRunning`) 
VALUES("The Blushing Ogress","Harkinal","Khu'Kran",4,1,"Guard-Duty Bouncer Labor","Character's learning new weapons may spend an additional 25gp to learn the weapon twice as fast while the fleet is in town.","",false,4,false,false);
--Basen
INSERT INTO `boats` (`boatName`, `city`, `country`, `waitTime`, `timeInTown`, `jobsAffected`, `tier2Ability`, `tableToGenerate`, `isTier2`, `weeksLeft`, `isInTown`, `isRunning`) 
VALUES("Congeleé Rafale","Basen","Blancis",5,2,"Taxi Journalism","If a character succeeds in training an animal, they gain one additional training point.","",false,5,false,false);
--Gambion
INSERT INTO `boats` (`boatName`, `city`, `country`, `waitTime`, `timeInTown`, `jobsAffected`, `tier2Ability`, `tableToGenerate`, `isTier2`, `weeksLeft`, `isInTown`, `isRunning`) 
VALUES("Salted Trader","Gambion","Quezzil",12,3,"Cooking Jungo's-Shop Docks Journalism","Character's that produce a meal during their downtime may produce one more of that meal (expending the necessary resources). If a character has heat, they may spend 50gp to reduce that heat by 1 while the fleet is in town.","",false,12,false,false);
--Lyron
INSERT INTO `boats` (`boatName`, `city`, `country`, `waitTime`, `timeInTown`, `jobsAffected`, `tier2Ability`, `tableToGenerate`, `isTier2`, `weeksLeft`, `isInTown`, `isRunning`) 
VALUES("The Shrouded Serpent","Lyron","Dunish",9,3,"Alchemist Brewing Theft","The DC for successful gambling increases by 4 instead of 5 while the fleet is in town. Characters proficient in any gaming set may enter a special and spend 100gp to play a special Dunish lottery. They may roll 1d20+1, on a 21 they win a special prize.","",false,9,false,false);
--Shinzhou
INSERT INTO `boats` (`boatName`, `city`, `country`, `waitTime`, `timeInTown`, `jobsAffected`, `tier2Ability`, `tableToGenerate`, `isTier2`, `weeksLeft`, `isInTown`, `isRunning`) 
VALUES("U~Otakatta","Shinzhou","The Zhoulands",6,2,"Gardening Doctor","Characters can purchase special fertilizers from the fleet while it is in town. For a list see the Specialty Facility document.","",false,6,false,false);
--Mycerion
INSERT INTO `boats` (`boatName`, `city`, `country`, `waitTime`, `timeInTown`, `jobsAffected`, `tier2Ability`, `tableToGenerate`, `isTier2`, `weeksLeft`, `isInTown`, `isRunning`) 
VALUES("The Indomiatable II","Mycerion","Ocean's Valley",4,1,"Arcanist Teaching","Character's attempting research only have to play 25gp instead of 50gp to gain a +1 to their research check.","",false,6,false,false);

-- Trade Fleet
INSERT INTO `boats` (`boatName`, `city`, `country`, `waitTime`, `timeInTown`, `jobsAffected`, `tier2Ability`, `tableToGenerate`, `isTier2`, `weeksLeft`, `isInTown`, `isRunning`) 
VALUES("South Quezzillian Trade Fleet","N/A","Quezzillian",12,2,"","","",false,14,false,true);

-- Astral Sea Boat
INSERT INTO `boats` (`boatName`, `city`, `country`, `waitTime`, `timeInTown`, `jobsAffected`, `tier2Ability`, `tableToGenerate`, `isTier2`, `weeksLeft`, `isInTown`, `isRunning`) 
VALUES("Tl’a’ikith","Allport","Astral Sea",7,1,"Docks Theft Artist","While in town, characters can spend 50gp to hire a tutor for the week, allowing them to gain double progress on learning one tool or language, you still have to use your downtime to train and spend the 25gp needed to do so. In addition, character rolling for Complications in downtime can roll twice and select which result they want to use.","otherworldItems",false,7,false,false);

-- Create Shipment Table
CREATE TABLE shipment (
    id int NOT NULL AUTO_INCREMENT,
    itemName varchar(40),
    price int,
    quantity int,
    PRIMARY KEY (id)
);