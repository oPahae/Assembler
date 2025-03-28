SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `cmnts` (
  `id` int(11) NOT NULL,
  `taskID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `content` text NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cmnts` (`id`, `taskID`, `userID`, `content`, `createdAt`) VALUES
(1, 3, 3, 'Dakshi saraha naaaaaaaaaadi', '2025-03-05 16:13:06'),
(2, 3, 2, 'Nn khari', '2025-03-05 16:13:21'),
(3, 3, 1, 'Pin o pin', '2025-03-06 17:19:24'),
(4, 5, 5, 'yarpi ikhdm hhhhhhhhhh', '2025-03-13 11:17:23');

CREATE TABLE `docs` (
  `id` int(11) NOT NULL,
  `content` longblob NOT NULL,
  `taskID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `size` float NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `deadline` date DEFAULT current_timestamp(),
  `projectID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `events` (`id`, `name`, `deadline`, `projectID`) VALUES
(1, 'slm hh', '2025-03-20', 1);

CREATE TABLE `msgs` (
  `id` int(11) NOT NULL,
  `projectID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `msgID` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `msgs` (`id`, `projectID`, `userID`, `msgID`, `content`, `createdAt`) VALUES
(1, 1, 2, NULL, 'ccccccccccccccccccccc', '2025-03-06 17:58:50'),
(2, 1, 1, NULL, 'hi', '2025-03-06 18:00:54'),
(3, 1, 1, NULL, 'cc', '2025-03-06 18:15:54'),
(4, 1, 1, NULL, 'cc', '2025-03-11 10:03:16'),
(5, 1, 5, NULL, 'cc', '2025-03-11 10:12:52'),
(10, 1, 6, NULL, 'cc', '2025-03-11 10:31:40'),
(11, 1, 6, NULL, 'hi', '2025-03-11 10:36:07'),
(12, 1, 6, NULL, 'hi', '2025-03-11 10:36:11'),
(13, 1, 9, NULL, 'cc', '2025-03-11 16:10:14'),
(14, 1, 5, NULL, 'hi', '2025-03-13 13:56:42'),
(15, 1, 5, NULL, 'nn', '2025-03-13 13:57:33'),
(16, 1, 5, NULL, 'cc', '2025-03-13 14:01:35');

CREATE TABLE `profs` (
  `id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `img` varchar(255) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `deadline` date NOT NULL,
  `maxMembers` int(11) NOT NULL,
  `slogan` text DEFAULT NULL,
  `type` text DEFAULT NULL,
  `code` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `projects` (`id`, `userID`, `name`, `deadline`, `maxMembers`, `slogan`, `type`, `code`) VALUES
(1, 1, 'Marketing', '2025-03-12', 10, 'Slm hh', 'Informatique', '8jY9fqRtdx'),
(3, 9, 'PFE', '2025-03-20', 4, 'Nn hh', 'Réseaux', 'uX9i876gHty'),
(4, 1, 'Proj1', '2025-03-21', 8, 'Slm hh', 'Développement de Jeux Vidéo', '5FS6OG4Y');

CREATE TABLE `subtasks` (
  `id` int(11) NOT NULL,
  `taskID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` varchar(100) DEFAULT 'todo',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `subtasks` (`id`, `taskID`, `name`, `status`, `createdAt`) VALUES
(1, 3, 'Profile page', 'done', '2025-03-05 15:57:56'),
(2, 3, 'Dashboard page', 'done', '2025-03-05 15:58:18'),
(3, 3, 'Backend', 'done', '2025-03-06 16:59:55'),
(4, 3, 'Nothing', 'done', '2025-03-06 17:20:20'),
(5, 5, 'n9ra uml', 'done', '2025-03-13 11:16:53'),
(6, 5, 'n7fd td', 'todo', '2025-03-13 11:17:01'),
(7, 6, 'cc', 'done', '2025-03-13 15:15:35'),
(8, 6, 'nn', 'todo', '2025-03-13 15:29:57'),
(9, 6, 'hh', 'done', '2025-03-13 15:30:14'),
(10, 4, '1', 'todo', '2025-03-13 15:35:10'),
(11, 4, '2', 'todo', '2025-03-13 15:35:15'),
(12, 4, '3', 'todo', '2025-03-13 15:35:18'),
(13, 4, '4', 'todo', '2025-03-13 15:35:21'),
(14, 4, '5', 'todo', '2025-03-13 15:35:28');

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `projectID` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `deadline` date NOT NULL,
  `status` varchar(100) DEFAULT 'TODO',
  `descr` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tasks` (`id`, `projectID`, `userID`, `name`, `deadline`, `status`, `descr`, `createdAt`) VALUES
(3, 1, 1, 'Frontend', '2025-03-15', 'done', 'Slm hh', '2025-03-05 10:48:22'),
(4, 1, 1, 'Backend', '2025-03-19', 'ongoing', 'Slm hhhhhhh (x2)', '2025-03-11 17:29:51'),
(5, 1, 8, 'Conception', '2025-03-20', 'ongoing', 'Urgennnnnnnnnnt !', '2025-03-13 10:58:50'),
(6, 1, 1, 'Deployement', '2025-03-22', 'ongoing', 'Slm hh (x3)', '2025-03-13 15:05:40');

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `img` varchar(255) DEFAULT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'student',
  `speciality` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `firstname`, `lastname`, `email`, `password`, `img`, `role`, `speciality`) VALUES
(1, 'pahae', 'test', 'pahae@gmail.com', '111111', NULL, 'student', ''),
(2, 'Hala', 'Pelmo', 'hal@gmai.lcom', '$2y$10$dd3I1cSxF0Yz1EqQdl.nV.sl033it8r0Mw5VJlzC/pkiCVMa5e4ne', NULL, 'student', ''),
(3, 'Ayop', 'Mohip', 'ayop@gmail.com', '$2y$10$5YaCQ2gOeE/VFannF8IM2uUD2fZ06AYI6c7HLDgIcluj60wx0c7FK', NULL, 'student', ''),
(4, 'Wahip', 'Lklp', 'wah.klp@gmail.com', '$2y$10$jXXzKrKXNu6ZpzhiWM3P3eH5ZdVrNLWdE1dLaDuk0ZHL.pDaeIIPW', NULL, 'student', ''),
(5, 'التائب إلى الله', '', 'ioryiron07170@gmail.com', '$2y$10$WRsw5V3LBR5HVCHqCqMnDuZO.nKtHhHIbOhTUsjj3UR/XscYVY.XG', 'https://lh3.googleusercontent.com/a/ACg8ocJSUVxM-splu9c2-5pgbD6QlYP5tCdfPJyWnoavXFG-IHPJrq-t=s96-c', 'student', ''),
(6, 'pahae', 'wlh', 'lam.bahae7@gmail.com', '$2y$10$YUeLT1WyO6bDUylJMPAir.OT2bMfLlDrWOfyWgArqURtkb1O23HI6', NULL, 'student', ''),
(7, 'OBSIDIAN', '', 'obsidiannoreply99@gmail.com', '$2y$10$WGoz.BIkQMqoejvfwS5FT.9lex3K96Cc.Ei1Qmsy5bOT24bpfZWNm', 'https://lh3.googleusercontent.com/a/ACg8ocI2U7VZr1h99uWDfkFvyQefgYvDcmOFWqFQlArAWk3o72bbqg=s96-c', 'student', ''),
(8, 'LAMRISSI', 'Bahae-eddine', 'ackerman07170@gmail.com', '$2y$10$ZYOXfxNYUUKWaEaaWOj/T.CturNUoaOH6SJE8oWisCmMx5xd.Vxm2', 'https://lh3.googleusercontent.com/a/ACg8ocLFVyyXFwZqA_pLRkciF31J3EJTCPdheoy3CosFBi_QIIDBS8tx=s96-c', 'student', ''),
(9, 'Zenp', 'Ras Taro', 'zenp.emo@smnt7.com', '$2y$10$hmbwlUOrkqlgz8GcklfIweJfR9UgkgaACC/nHzWPLYgaXq7gcMZy.', NULL, 'student', ''),
(10, 'test', 'test', 'test', 'test', NULL, 'student', ''),
(11, 'Hicham', 'Benalla', 'fstsgi2022@gmail.com', '$2y$10$0//gYWUIqmRECGshL4/7LO2hP3aVPV8XR1zWu3rdZklPPwj612jOO', NULL, 'prof', 'Analyse de Données');

CREATE TABLE `uspr` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `projectID` int(11) NOT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `uspr` (`id`, `userID`, `projectID`, `role`) VALUES
(6, 1, 1, 'member'),
(7, 2, 1, 'member'),
(10, 2, 1, 'member'),
(14, 9, 1, 'member'),
(15, 8, 1, 'member'),
(16, 9, 3, 'member'),
(17, 5, 1, 'member');

CREATE TABLE `ustsk` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `taskID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `verifcodes` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `expiry` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `cmnts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `taskID` (`taskID`),
  ADD KEY `userID` (`userID`);

ALTER TABLE `docs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `taskID` (`taskID`);

ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projectID` (`projectID`);

ALTER TABLE `msgs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projectID` (`projectID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `msgID` (`msgID`);

ALTER TABLE `profs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userID` (`userID`);

ALTER TABLE `subtasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `taskID` (`taskID`);

ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projectID` (`projectID`),
  ADD KEY `userID` (`userID`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `uspr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projectID` (`projectID`);

ALTER TABLE `ustsk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_task` (`userID`,`taskID`),
  ADD KEY `taskID` (`taskID`);

ALTER TABLE `verifcodes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cmnts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `docs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `msgs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

ALTER TABLE `profs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `subtasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `uspr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

ALTER TABLE `ustsk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `verifcodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `cmnts`
  ADD CONSTRAINT `cmnts_ibfk_1` FOREIGN KEY (`taskID`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cmnts_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `docs`
  ADD CONSTRAINT `docs_ibfk_1` FOREIGN KEY (`taskID`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`projectID`) REFERENCES `projects` (`id`);

ALTER TABLE `msgs`
  ADD CONSTRAINT `msgs_ibfk_1` FOREIGN KEY (`projectID`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `msgs_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `msgs_ibfk_3` FOREIGN KEY (`msgID`) REFERENCES `msgs` (`id`) ON DELETE SET NULL;

ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `subtasks`
  ADD CONSTRAINT `subtasks_ibfk_1` FOREIGN KEY (`taskID`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`projectID`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `uspr`
  ADD CONSTRAINT `uspr_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `uspr_ibfk_2` FOREIGN KEY (`projectID`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

ALTER TABLE `ustsk`
  ADD CONSTRAINT `ustsk_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ustsk_ibfk_2` FOREIGN KEY (`taskID`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;
COMMIT;