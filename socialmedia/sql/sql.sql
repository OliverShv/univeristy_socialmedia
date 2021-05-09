CREATE DATABASE dbs;

CREATE TABLE accounts(
    user_id INT(8) NOT NULL,
    email VARCHAR(32) NOT NULL UNIQUE,
    password VARCHAR(72) NOT NULL,
    salt VARCHAR(13) NOT NULL,
    unix_timestamp INT(10) NOT NULL,
    fname VARCHAR(35) NOT NULL,
    lname VARCHAR(35) NOT NULL,
    cyear INT(8),
    picture VARCHAR(64) DEFAULT 'default.png',
    bio VARCHAR(1000),
	CONSTRAINT user_pk_index PRIMARY KEY(user_id)
);

CREATE TABLE session(
    session_id INT NOT NULL AUTO_INCREMENT,
    generatedCode VARCHAR(36) NOT NULL,
    account_id INT NOT NULL,
    unix_timestamp INT(10) NOT NULL,
    status VARCHAR(8) NOT NULL,
    CONSTRAINT session_id_index PRIMARY KEY(session_id),
    FOREIGN KEY (account_id) REFERENCES accounts(user_id) 
);

CREATE TABLE connections(
    connection_id INT NOT NULL AUTO_INCREMENT,
    session_id INT NOT NULL,
    client_id INT NOT NULL,
    type VARCHAR(8) NOT NULL,
    chat_id VARCHAR(12),
    status VARCHAR(8),
    CONSTRAINT connection_id_index PRIMARY KEY(connection_id),
    FOREIGN KEY (session_id) REFERENCES session(session_id) 
);

CREATE TABLE courses(
    course_id VARCHAR(4) NOT NULL,
    cname VARCHAR(100) NOT NULL UNIQUE,
    years INT(1) NOT NULL,
	CONSTRAINT course_pk_index PRIMARY KEY(course_id)
);

CREATE TABLE modules(
    module_id VARCHAR(8) NOT NULL,
    mname VARCHAR(100) NOT NULL,
	CONSTRAINT module_pk_index PRIMARY KEY(module_id)
);

CREATE TABLE studentcourse(
    sc_id INT(8) AUTO_INCREMENT NOT NULL,
    course_id VARCHAR(4) NOT NULL,
    student_id INT(8) NOT NULL,
	CONSTRAINT scid_pk_index PRIMARY KEY(sc_id),
    FOREIGN KEY (course_id) REFERENCES courses(course_id),
    FOREIGN KEY (student_id) REFERENCES accounts(user_id) 
);

CREATE TABLE studentmodule(
    sm_id INT(8) AUTO_INCREMENT NOT NULL,
    module_id VARCHAR(8) NOT NULL,
    student_id INT(8) NOT NULL,
    CONSTRAINT smid_pk_index PRIMARY KEY(sm_id),
    FOREIGN KEY (module_id) REFERENCES modules(module_id),
    FOREIGN KEY (student_id) REFERENCES accounts(user_id) 
);

CREATE TABLE modulecourse(
    mc_id INT(8) AUTO_INCREMENT NOT NULL,
    module_id VARCHAR(8) NOT NULL,
    course_id VARCHAR(4) NOT NULL,
    mandatory VARCHAR(5),
    course_year INT(1),
    CONSTRAINT mcid_pk_index PRIMARY KEY(mc_id),
    FOREIGN KEY (module_id) REFERENCES modules(module_id),
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);

CREATE TABLE personalmessages(
    message_id INT NOT NULL AUTO_INCREMENT,
    sender_id INT(8) NOT NULL,
    receiver_id INT(8) NOT NULL,
    message VARCHAR(2500) NOT NULL,
    unix_timestamp INT NOT NULL,
    seen varchar(3), 
    CONSTRAINT message_pm_pk_index PRIMARY KEY(message_id),
    FOREIGN KEY (sender_id) REFERENCES accounts(user_id),
    FOREIGN KEY (receiver_id) REFERENCES accounts(user_id)
);

CREATE TABLE cmmessages(
    message_id INT NOT NULL AUTO_INCREMENT,
    sender_id INT(8) NOT NULL,
    group_id LONGTEXT NOT NULL,
    message VARCHAR(2500) NOT NULL,
    unix_timestamp INT NOT NULL,
    CONSTRAINT message_cm_pk_index PRIMARY KEY(message_id),
    FOREIGN KEY (sender_id) REFERENCES accounts(user_id)
);

CREATE TABLE groups(
    group_id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(20) NOT NULL,
    admin_id INT(8) NOT NULL,
    status VARCHAR(8) NOT NULL,
    unix_timestamp INT NOT NULL,
    CONSTRAINT group_pk_index PRIMARY KEY(group_id),
    FOREIGN KEY (admin_id) REFERENCES accounts(user_id)
);

CREATE TABLE groupmembers(
    groupmembers_id INT NOT NULL AUTO_INCREMENT,
    group_id INT NOT NULL,
    user_id INT(8) NOT NULL,
    status VARCHAR(8) NOT NULL,
    CONSTRAINT hroup_member_pk_index PRIMARY KEY(groupmembers_id),
    FOREIGN KEY (user_id) REFERENCES accounts(user_id)
);

CREATE TABLE groupmessages(
    message_id INT AUTO_INCREMENT,
    sender_id INT(8) NOT NULL,
    group_id INT NOT NULL,
    message VARCHAR(2500) NOT NULL,
    unix_timestamp INT NOT NULL,
    CONSTRAINT message_group_pk_index PRIMARY KEY(message_id),
    FOREIGN KEY (sender_id) REFERENCES accounts(user_id),
    FOREIGN KEY (group_id) REFERENCES groups(group_id)
);

INSERT INTO courses(course_id, cname, years)
VALUES("COMP","Computing BSC","3"),
("PHED","Physical Education BSC","3"),
("BUFI","Business & Finance BA","3"),
("ENLI","English Literature BA","3"),
("BIOL","Biology BSC","3");


INSERT INTO modules(module_id,mname)
VALUES("COMP1938","Foundations of Computing"),
("COMP1344","Introduction to OO Programming"),
("COMP1637","Web Technologies"),
("COMP1763","IT Systems Fundamentals OR Language module"),
("COMP1824","Creative Computing OR Language module"),

("COMP2675","Systems Analysis and Design"),
("COMP2326","Distributed Systems OR OO Design and Development OR Web Application Development"),
("COMP2231","Distributed Systems"),
("COMP2543","OO Design and Development"),
("COMP2756","Web Application Development"),
("COMP2926","Data Mining"),
("COMP2372","Game Design and Engineering"),
("COMP2193","Consultancy and Research Methods"),

("COMP3234","Computing Project"),
("COMP3525","Nature of Computing"),
("COMP3853","Applied Software Engineering"),
("COMP3367","IT Systems Consultancy"),
("COMP3953","Applied Drone Technology"),
("COMP3254","Machine Learning"),
("COMP3153","Cyber Security"),
("COMP3653","Digital Business"),

("PHED1255","Introduction to Physical Education"),
("PHED1436","Scientific Principles in Teaching and Coaching"),
("PHED1768","Dance and Gymnastics in Primary Schools"),
("PHED1976","Adapted Physical Activity, Sport and Disability"),
("PHED1256","Physical Activity, Health and Games"),

("PHED2256","Learning and Teaching through Games"),
("PHED2215","Advanced Scientific Principles in Teaching and Coaching"),
("PHED2692","Creating Successful Research"),
("PHED2037","Teaching Special Education Needs and Disability PE in Schools"),
("PHED2285","Teaching Gymnastics in Secondary Schools"),
("PHED2926","Teaching Dance in Secondary Schools"),
("PHED2732","Contemporary Issues in Sport"),
("PHED2947","Swimming, Lifesaving and risk"),

("PHED3157","Independent Study"),
("PHED3475","School based Placement"),
("PHED3973","Professional Placement"),
("PHED3536","The Developing Child"),
("PHED3527","14-19 Curriculum"),
("PHED3725","Leading and Developing PE in Primary School"),
("PHED3493","Contemporary issues in disability sports coaching and PE"),
("PHED3384","Group Dynamics in Sport"),

("BUFI1035","Unlocking Individual Potential"),
("BUFI1144","Customer Insight and Marketing"),
("BUFI1624","Financial Management"),
("BUFI1445","Generation Digital"),
("BUFI1364","Data Driven Decisions"),

("BUFI2246","Unlocking Organisational Potential"),
("BUFI2843","Financial Markets and Investment"),
("BUFI2545","The Global Economy"),
("BUFI2895","Fundamentals of Management Accounting"),
("BUFI2537","Fundamentals of Financial Accounting"),
("BUFI2356","SME Management"),
("BUFI2956","Consultancy and Research Method"),
("BUFI2254","PR and Campaigning"),

("BUFI3545","International Business Strategy"),
("BUFI3235","Strategic Financial Management"),
("BUFI3846","International Banking and Finance"),
("BUFI3345","Digital and Social Media Marketing"),
("BUFI3994","Spin Doctors and Other Persuaders"),
("BUFI3853","Advertising and Digital Communications"),
("BUFI3183","Corporate Reporting and Performance Management"),
("BUFI3257","Taxation"),

("ENLI1256","Literary Forms and Genres"),
("ENLI1437","Exploring the Canon"),
("ENLI1834","Ways of Reading, Ways of Writing"),
("ENLI1945","Places and Spaces"),
("ENLI1045","Bodies and Beings"),

("ENLI2145","Exploring the Canon: Theory and Practice"),
("ENLI2264","Movement and Migration"),
("ENLI2523","Politics, Sex and Identity in the Early Modern World"),
("ENLI2724","Shakespeare: Stage, Page and Screen"),
("ENLI2634","Gothic and Romantic Literature"),
("ENLI2343","Spaces of Modernity"),
("ENLI2113","Children's Literature"),
("ENLI2632","Work Project"),

("ENLI3103","Independent Research Project"),
("ENLI3132","Justice and Revenge: from Tragedy to the Western"),
("ENLI3546","Postcolonial Encounters"),
("ENLI3265","Writing and the Environment"),
("ENLI3654","War and Conflict"),
("ENLI3364","Gendering Voices"),
("ENLI3783","Partnerships and Rivalries"),
("ENLI3003","Literatures and Cultures: International Explorations"),

("BIOL1734","Introduction to Ecology"),
("BIOL1042","Animal Diversity"),
("BIOL1253","Cell Biology"),
("BIOL1751","Comparative Animal Physiology"),
("BIOL1471","Introduction to Human Anatomy and Physiology"),

("BIOL2174","Plant Biology"),
("BIOL2047","Project and Career Development"),
("BIOL2634","Molecular and Cellular Biology"),
("BIOL2837","Molecular Genetics and Conservation"),
("BIOL2347","Work Experience"),
("BIOL2475","Microbiology"),
("BIOL2947","Infectious Agents and Allergens"),
("BIOL2739","Animal Senses and Survival"),

("BIOL3027","Independent Study"),
("BIOL3325","Plant Development and Physiology"),
("BIOL3624","Mammalian Reproduction"),
("BIOL3853","Animal Movement"),
("BIOL3937","Forensic DNA Analysis"),
("BIOL3485","Biological Indicators for Crime Reporting"),
("BIOL3138","Pharmacology"),
("BIOL3244","Genomics and Bioinformatics");

INSERT INTO modulecourse(course_id,module_id,mandatory,course_year)
VALUES("COMP","COMP1938","TRUE","1"),
("COMP","COMP1344","TRUE","1"),
("COMP","COMP1637","TRUE","1"),
("COMP","COMP1763","TRUE","1"),
("COMP","COMP1824","TRUE","1"),

("COMP","COMP2675","TRUE","2"),
("COMP","COMP2326","TRUE","2"),
("COMP","COMP2231","FALSE","2"),
("COMP","COMP2543","FALSE","2"),
("COMP","COMP2756","FALSE","2"),
("COMP","COMP2926","FALSE","2"),
("COMP","COMP2372","FALSE","2"),
("COMP","COMP2193","FALSE","2"),

("COMP","COMP3234","TRUE","3"),
("COMP","COMP3525","TRUE","3"),
("COMP","COMP3853","FALSE","3"),
("COMP","COMP3367","FALSE","3"),
("COMP","COMP3953","FALSE","3"),
("COMP","COMP3254","FALSE","3"),
("COMP","COMP3153","FALSE","3"),
("COMP","COMP3653","FALSE","3"),

("PHED","PHED1255","TRUE","1"),
("PHED","PHED1436","TRUE","1"),
("PHED","PHED1768","TRUE","1"),
("PHED","PHED1976","TRUE","1"),
("PHED","PHED1256","FALSE","1"),

("PHED","PHED2256","TRUE","2"),
("PHED","PHED2215","TRUE","2"),
("PHED","PHED2692","TRUE","2"),
("PHED","PHED2037","FALSE","2"),
("PHED","PHED2285","FALSE","2"),
("PHED","PHED2926","FALSE","2"),
("PHED","PHED2732","FALSE","2"),
("PHED","PHED2947","FALSE","2"),

("PHED","PHED3157","TRUE","3"),
("PHED","PHED3475","FALSE","3"),
("PHED","PHED3973","FALSE","3"),
("PHED","PHED3536","FALSE","3"),
("PHED","PHED3527","FALSE","3"),
("PHED","PHED3725","FALSE","3"),
("PHED","PHED3493","FALSE","3"),
("PHED","PHED3384","FALSE","3"),

("BUFI","BUFI1035","TRUE","1"),
("BUFI","BUFI1144","TRUE","1"),
("BUFI","BUFI1624","TRUE","1"),
("BUFI","BUFI1445","TRUE","1"),
("BUFI","BUFI1364","FALSE","1"),

("BUFI","BUFI2246","TRUE","2"),
("BUFI","BUFI2843","TRUE","2"),
("BUFI","BUFI2545","TRUE","2"),
("BUFI","BUFI2895","FALSE","2"),
("BUFI","BUFI2537","FALSE","2"),
("BUFI","BUFI2356","FALSE","2"),
("BUFI","BUFI2956","FALSE","2"),
("BUFI","BUFI2254","FALSE","2"),

("BUFI","BUFI3545","TRUE","3"),
("BUFI","BUFI3235","TRUE","3"),
("BUFI","BUFI3846","TRUE","3"),
("BUFI","BUFI3345","FALSE","3"),
("BUFI","BUFI3994","FALSE","3"),
("BUFI","BUFI3853","FALSE","3"),
("BUFI","BUFI3183","FALSE","3"),
("BUFI","BUFI3257","FALSE","3"),

("ENLI","ENLI1256","TRUE","1"),
("ENLI","ENLI1437","TRUE","1"),
("ENLI","ENLI1834","TRUE","1"),
("ENLI","ENLI1945","FAlSE","1"),
("ENLI","ENLI1045","FAlSE","1"),

("ENLI","ENLI2145","TRUE","2"),
("ENLI","ENLI2264","TRUE","2"),
("ENLI","ENLI2523","FAlSE","2"),
("ENLI","ENLI2724","FAlSE","2"),
("ENLI","ENLI2634","FALSE","2"),
("ENLI","ENLI2343","FALSE","2"),
("ENLI","ENLI2113","FALSE","2"),
("ENLI","ENLI2632","FALSE","2"),

("ENLI","ENLI3103","TRUE","3"),
("ENLI","ENLI3132","FAlSE","3"),
("ENLI","ENLI3546","FALSE","3"),
("ENLI","ENLI3265","FALSE","3"),
("ENLI","ENLI3654","FALSE","3"),
("ENLI","ENLI3364","FALSE","3"),
("ENLI","ENLI3783","FALSE","3"),
("ENLI","ENLI3003","FALSE","3"),

("BIOL","BIOL1734","TRUE","1"),
("BIOL","BIOL1042","TRUE","1"),
("BIOL","BIOL1253","TRUE","1"),
("BIOL","BIOL1751","TRUE","1"),
("BIOL","BIOL1471","FAlSE","1"),

("BIOL","BIOL2174","TRUE","2"),
("BIOL","BIOL2047","TRUE","2"),
("BIOL","BIOL2634","TRUE","2"),
("BIOL","BIOL2837","TRUE","2"),
("BIOL","BIOL2347","FALSE","2"),
("BIOL","BIOL2475","FALSE","2"),
("BIOL","BIOL2947","FALSE","2"),
("BIOL","BIOL2739","FALSE","2"),

("BIOL","BIOL3027","TRUE","3"),
("BIOL","BIOL3325","TRUE","3"),
("BIOL","BIOL3624","FALSE","3"),
("BIOL","BIOL3853","FALSE","3"),
("BIOL","BIOL3937","FALSE","3"),
("BIOL","BIOL3485","FALSE","3"),
("BIOL","BIOL3138","FALSE","3"),
("BIOL","BIOL3244","FALSE","3");

INSERT INTO accounts(user_id,email,password,salt,unix_timestamp,fname,lname,cyear,picture,bio)
VALUES(12345678,"test01_17@uni.worc.ac.uk","$2y$10$aOQM/y5//xbjkFJ9zNUL7Oq5MbdPV.YaFtQofAxnT2IMbbQ9TuaVG","5ed7d78002e5e",1591203712,"John","Smith",3,"12345678.5ed7d8f7957852.44571184.jpg","My passions include programming and art :)"),
(13245768,"test03_17@uni.worc.ac.uk","$2y$10$DkmGpoucJOgcNxJ1ksOcruURFhwS0VZha5UdGws/eEvBpdss0t00G","5ed7ddbc69ac0",1591205308,"Emma","Cambridge",3,"13245768.5ed7deaf002e42.98976223.jpg","Message me if you need anything"),
(87654321,"test02_17@uni.worc.ac.uk","$2y$10$ZyYDO9NKYpVt4FcF58XG2OdoXhE3Vt0HVjQbXct/LU9prEYtLu6Ya","5ed7d9559eaed",1591204181,"Tom","Jones",3,"default.png","Named after a singer but got the vocal range of a Poundland walkie talkie");

INSERT INTO studentcourse(course_id,student_id)
VALUES("COMP",12345678),
("COMP",87654321),
("COMP",13245768);

INSERT INTO studentmodule(module_id,student_id)
VALUES("COMP3234",12345678),
("COMP3525",12345678),
("COMP3367",12345678),
("COMP3953",12345678),
("COMP3234",87654321),
("COMP3525",87654321),
("COMP3953",87654321),
("COMP3254",87654321),
("COMP3234",13245768),
("COMP3525",13245768),
("COMP3367",13245768),
("COMP3953",13245768),
("COMP3653",13245768);

INSERT INTO groups(name,admin_id,status,unix_timestamp)
VALUES("All users",12345678,"active",1591209828);

INSERT INTO groupmembers(group_id,user_id,status)
VALUES(1,87654321,"active"),
(1,13245768,"active"),
(1,12345678,"active");

INSERT INTO groupmessages(sender_id,group_id,message,unix_timestamp)
VALUES(12345678,1,"Hello",1591209864);

INSERT INTO personalmessages(sender_id,receiver_id,message,unix_timestamp,seen)
VALUES(12345678,87654321,"hi",1591209796,"yes"),
(87654321,12345678,"How are you?",1591210142,"yes"),
(12345678,87654321,"I'm doing well",1591210150,"yes"),
(12345678,13245768,"Message me when you are online!",1591210164,"yes");