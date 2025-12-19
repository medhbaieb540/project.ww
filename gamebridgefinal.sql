-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2025 at 03:08 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gamebridgefinal`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`) VALUES
(1, 'Action', 'Fast-paced action games'),
(2, 'Adventure', 'Exploration and story-driven games'),
(3, 'Puzzle', 'Brain-teasing puzzle games'),
(4, 'Horror', 'Scary and suspenseful games'),
(5, 'Racing', 'Racing and driving games'),
(6, 'Strategy', 'Strategic thinking games'),
(7, 'RPG', 'Role-playing games'),
(8, 'Sports', 'Sports and athletic games');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `edited_at` datetime DEFAULT NULL,
  `is_edited` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`, `edited_at`, `is_edited`) VALUES
(1, 1, 2, 'That\'s great! When????', '2025-11-28 07:57:43', NULL, 0),
(2, 5, 58, 'Hi! Welcome!', '2025-12-09 11:24:33', '2025-12-11 19:43:56', 1),
(3, 6, 60, 'Thanks! Excited to participate!', '2025-12-12 09:32:05', '2025-12-12 09:32:42', 1),
(4, 6, 2, 'See you there!', '2025-12-12 09:32:30', NULL, 0),
(5, 5, 61, 'sqddddddddddsqdqsdqs', '2025-12-17 17:37:44', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`id`, `owner_id`, `name`, `description`, `status`, `created_at`, `address`) VALUES
(16, 56, 'test7', 'tetas', 'active', '2025-12-14 09:34:33', 'test7'),
(17, 57, 'test8', 'test8', 'active', '2025-12-14 09:35:33', 'tes8');

-- --------------------------------------------------------

--
-- Table structure for table `company_members`
--

CREATE TABLE `company_members` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('owner','developer') DEFAULT 'developer',
  `status` enum('active','pending','blocked') DEFAULT 'active',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_members`
--

INSERT INTO `company_members` (`id`, `company_id`, `user_id`, `role`, `status`, `joined_at`) VALUES
(1, 16, 63, 'developer', 'active', '2025-12-18 18:42:05'),
(2, 17, 64, 'developer', 'active', '2025-12-18 19:42:36');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `organizer_id` int(11) NOT NULL,
  `eventdate` date DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `organizer_id`, `eventdate`, `max_participants`, `created_at`, `updated_at`) VALUES
(7, 'FC27 Beta testing', 'asfasfasfasf', 2, '2025-12-19', 20, '2025-12-18 17:56:26', '2025-12-18 17:56:26');

-- --------------------------------------------------------

--
-- Table structure for table `event_participation`
--

CREATE TABLE `event_participation` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_participation`
--

INSERT INTO `event_participation` (`id`, `user_id`, `event_id`, `joined_at`, `created_at`) VALUES
(7, 63, 7, '2025-12-18 18:43:14', '2025-12-18 18:43:14');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `favorite_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Links to users.id',
  `game_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) UNSIGNED NOT NULL,
  `game` varchar(100) NOT NULL COMMENT 'Game name',
  `type` enum('feedback','report') NOT NULL DEFAULT 'feedback',
  `message` text NOT NULL,
  `author` varchar(50) NOT NULL COMMENT 'Username of submitter',
  `status` enum('pending','reviewed','fixed') NOT NULL DEFAULT 'pending',
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `game`, `type`, `message`, `author`, `status`, `date`, `user_id`) VALUES
(1, 'Space Shooter X', 'report', 'Game crashes when I reach level 5. Error appears right after boss fight starts.', 'u2', 'pending', '2025-12-15 10:30:00', 2),
(2, 'Racing Legends', 'feedback', 'Love the graphics! But the controls feel a bit sluggish on mobile devices.', 'u3', 'reviewed', '2025-12-15 14:20:00', 3),
(3, 'Space Shooter X', 'report', 'Cannot save progress after completing missions. Progress resets on restart.', 'u4', 'fixed', '2025-12-14 09:15:00', 4),
(4, 'Medieval Quest', 'feedback', 'Amazing game! The storyline is engaging. Would love to see multiplayer mode added.', 'u5', 'reviewed', '2025-12-16 16:45:00', 5),
(5, 'Racing Legends', 'report', 'Collision detection seems off. Cars pass through walls sometimes.', 'test6', 'pending', '2025-12-16 11:30:00', 54),
(6, 'Medieval Quest', 'feedback', 'The soundtrack is incredible! Great work on the audio design.', 'test7', 'pending', '2025-12-17 08:00:00', 55),
(7, 'Space Shooter X', 'feedback', 'Weapon upgrade system needs balancing. Laser gun is too overpowered.', 'test8', 'reviewed', '2025-12-16 20:30:00', 56),
(8, 'Racing Legends', 'report', 'Multiplayer lobby disconnects frequently. Cannot finish races with friends.', 'MohamedRayen.Chihi', 'pending', '2025-12-17 09:15:00', 57),
(9, 'Medieval Quest', 'report', 'Quest log bug: Completed quests still show as active in the journal.', 'u2', 'reviewed', '2025-12-15 18:30:00', 2),
(10, 'Space Shooter X', 'feedback', 'Could you add controller support? Would make the game more accessible.', 'saif', 'pending', '2025-12-17 10:00:00', 60),
(14, 'valorant', 'feedback', 'it takes many time in my day', 'ayman', 'fixed', '2025-12-18 22:09:43', 63),
(23, 'asdasd', 'feedback', 'asdasdasdasd', 'ayman', 'reviewed', '2025-12-18 23:04:37', 63),
(24, 'aymanplayer', 'feedback', 'ayman is the best player in the world', 'med12', 'pending', '2025-12-19 00:20:36', 62);

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `game_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `developer_id` int(11) NOT NULL COMMENT 'Links to users.id',
  `category_id` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `average_rating` decimal(3,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`game_id`, `title`, `description`, `developer_id`, `category_id`, `image_path`, `file_path`, `average_rating`, `created_at`, `is_deleted`, `deleted_at`) VALUES
(7, 'schedule 1', 'Schedule I est un prochain jeu vidéo de simulation criminelle en monde ouvert développé par le développeur australien TVGS.', 63, 1, 'uploads/images/694433d938cb1_1766077401.png', 'uploads/694433d9390f7_1766077401.rar', 4.00, '2025-12-18 17:03:21', 0, NULL),
(8, 'ROUNDS', 'Affronte d\'autres joueurs dans des duels déchaînés ! Tire, pare et saute jusqu\'à la victoire avec ton fusil à pompe lance-roquettes qui envoie des missiles à tête chercheuse sensibles à la chaleur, ou toute autre combinaison de pouvoirs parmi plus de 11,2 millions de possibilités.', 63, 1, 'uploads/images/694437dded2f5_1766078429.png', 'uploads/694437dded7b4_1766078429.rar', 0.00, '2025-12-18 17:20:29', 0, NULL),
(9, 'Overcooked 2', 'Overcooked! 2 est un jeu vidéo de simulation de cuisine coopératif développé par Ghost Town Games et Team17 et publié par Team17.', 60, 2, 'uploads/images/6944383a90a3a_1766078522.png', 'uploads/6944383a90d5b_1766078522.zip', 0.00, '2025-12-18 17:22:02', 0, NULL),
(10, 'DEVOUR', 'Devour est un jeu vidéo d\'horreur coopératif PvE créé par les développeurs Straight Back Games, jouable par 1 à 4 joueurs.', 63, 4, 'uploads/images/694438600c60e_1766078560.png', 'uploads/694438600cb77_1766078560.rar', 0.00, '2025-12-18 17:22:40', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `edited_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`, `edited_at`) VALUES
(1, 60, 2, 'Hi! Great to meet you!', 0, '2025-12-04 23:30:52', NULL),
(2, 60, 2, 'Looking forward to the events!', 0, '2025-12-05 09:35:54', NULL),
(3, 2, 60, 'Welcome! Let me know if you need any help.', 1, '2025-12-09 11:33:04', NULL),
(4, 60, 58, 'Thanks for organizing these tournaments!', 1, '2025-12-09 11:33:30', NULL),
(5, 58, 60, 'You\'re welcome! Glad to have you here.', 1, '2025-12-09 11:34:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `organizer`
--

CREATE TABLE `organizer` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organizer`
--

INSERT INTO `organizer` (`id`, `user_id`, `display_name`, `email`, `created_at`) VALUES
(2, 2, 'sayf', 'saifamam251@gmail.com', '2025-12-17 10:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `participations`
--

CREATE TABLE `participations` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `participations`
--

INSERT INTO `participations` (`id`, `tournament_id`, `user_id`, `joined_at`) VALUES
(3, 41, 63, '2025-12-18 22:04:46'),
(4, 42, 63, '2025-12-18 22:08:46'),
(5, 42, 62, '2025-12-19 00:17:11'),
(8, 41, 62, '2025-12-19 00:52:22');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `edited_at` datetime DEFAULT NULL,
  `is_edited` tinyint(1) DEFAULT 0,
  `category` varchar(50) DEFAULT 'General',
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `content`, `image`, `created_at`, `edited_at`, `is_edited`, `category`, `is_archived`) VALUES
(1, 2, 'NEW GAME WILL BE AVAILABLE SOON', '1764313004_post.png', '2025-11-28 07:56:44', NULL, 0, 'General', 0),
(2, 2, 'Amazing tournament coming up!', '1764420702_6645725.png', '2025-11-29 13:51:42', NULL, 0, 'General', 0),
(3, 60, 'Just joined GameBridge! Excited to be part of this community!', '', '2025-12-04 20:03:01', '2025-12-04 20:03:21', 1, 'General', 0),
(4, 58, 'Welcome to all new members! Check out our latest tournaments.', '', '2025-12-09 10:52:28', '2025-12-11 20:37:09', 1, 'Action', 0),
(5, 2, 'Hello everyone!', '', '2025-12-09 11:11:07', '2025-12-12 09:41:01', 1, 'Adventure', 0),
(6, 60, 'Looking forward to the next event!', '', '2025-12-12 09:31:19', NULL, 0, 'Action', 0),
(8, 62, 'ana med', NULL, '2025-12-18 19:23:54', NULL, 0, 'Action', 0);

-- --------------------------------------------------------

--
-- Table structure for table `post_replies`
--

CREATE TABLE `post_replies` (
  `id` int(11) NOT NULL,
  `comment_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `edited_at` datetime DEFAULT NULL,
  `is_edited` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_replies`
--

INSERT INTO `post_replies` (`id`, `comment_id`, `user_id`, `content`, `created_at`, `edited_at`, `is_edited`) VALUES
(1, 1, 2, 'In March!', '2025-11-28 07:58:42', NULL, 0),
(2, 2, 58, 'Thank you!', '2025-12-09 11:24:41', NULL, 0),
(3, 3, 60, 'Can\'t wait!', '2025-12-12 09:32:12', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `reactions`
--

CREATE TABLE `reactions` (
  `id` int(11) NOT NULL,
  `target` varchar(50) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `type` enum('like','dislike') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reactions`
--

INSERT INTO `reactions` (`id`, `target`, `username`, `type`) VALUES
(1, 'post1', 'u2', 'like'),
(2, 'post2', 'saif', 'like'),
(3, 'post3', 'u3', 'like'),
(4, 'post4', 'MohamedRayen.Chihi', 'like'),
(5, 'post5', 'test7', 'like'),
(6, 'post6', 'u4', 'like');

-- --------------------------------------------------------

--
-- Table structure for table `replies`
--

CREATE TABLE `replies` (
  `id` int(11) UNSIGNED NOT NULL,
  `feedback_id` int(11) UNSIGNED NOT NULL,
  `author` varchar(50) NOT NULL COMMENT 'Username of person replying',
  `message` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `replies`
--

INSERT INTO `replies` (`id`, `feedback_id`, `author`, `message`, `date`) VALUES
(1, 1, 'test7', 'Thanks for reporting! We are investigating this crash. Can you tell us what device you are using?', '2025-12-15 12:00:00'),
(2, 1, 'u2', 'I am using Windows 11, RTX 3060. Happens every time at the same spot.', '2025-12-15 13:15:00'),
(3, 2, 'test8', 'Appreciate the feedback! We will work on optimizing mobile controls in the next update.', '2025-12-15 15:30:00'),
(4, 3, 'MohamedRayen.Chihi', 'This issue has been fixed in version 1.2.3. Please update your game and let us know if it persists.', '2025-12-14 16:00:00'),
(5, 4, 'test7', 'Thank you! Multiplayer is definitely on our roadmap for Q1 2026. Stay tuned!', '2025-12-16 18:00:00'),
(6, 7, 'test8', 'Good point about weapon balancing. We will adjust the laser gun damage in the next patch.', '2025-12-16 22:00:00'),
(7, 8, 'MohamedRayen.Chihi', 'We are aware of the server issues. Our team is working on improving connection stability.', '2025-12-17 09:45:00'),
(8, 8, 'test7', 'Patch is being deployed this evening. Should resolve the disconnection problems.', '2025-12-17 10:30:00'),
(9, 9, 'test8', 'Thanks for catching this bug! Fixed in the latest hotfix. Clear your cache and restart.', '2025-12-15 19:30:00'),
(10, 10, 'test7', 'Controller support is coming in version 2.0! We are testing it now with Xbox and PlayStation controllers.', '2025-12-17 10:45:00'),
(11, 14, 'ayman', 'hellooo', '2025-12-18 22:55:44');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Links to users.id',
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `game_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(9, 7, 62, 4, 'asdasdasdasd', '2025-12-18 17:13:02');

-- --------------------------------------------------------

--
-- Table structure for table `rewards`
--

CREATE TABLE `rewards` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `value` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rewards`
--

INSERT INTO `rewards` (`id`, `title`, `value`, `type`) VALUES
(1, 'Neon Coins Pack', '200', 'points'),
(2, 'Champion Cash Prize', '500', 'cash'),
(3, 'Elite Badge', '1', 'badge'),
(4, 'aaa', '200', 'cash'),
(5, 'Money', '1000', 'cash'),
(7, 'aaaaaaaaaa', '2265', 'cash'),
(8, 'aaaaaaaaaaaa', '100', 'cash'),
(9, 'aaaaaaaaaaaa', '100', 'cash');

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `reward_id` int(11) UNSIGNED NOT NULL,
  `max_players` int(11) NOT NULL DEFAULT 16,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournaments`
--

INSERT INTO `tournaments` (`id`, `name`, `description`, `start_date`, `end_date`, `reward_id`, `max_players`, `image_path`) VALUES
(41, 'valorant champions', '', '2025-12-19 20:00:00', '2025-12-19 22:00:00', 2, 10, '../../assets/images/tournaments/1766080191_e5f34c6bfd54b8eb3ef73cb9750865ce728cb54c-1920x1080.png'),
(42, 'fc26 champions league', '', '2025-12-20 19:00:00', '2025-12-20 21:00:00', 2, 2, '../../assets/images/tournaments/1766080252_uefa-champions-league-deep-dive-2025-thumbnail.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(40) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_role` varchar(40) NOT NULL,
  `email` varchar(40) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `is_banned` tinyint(1) NOT NULL DEFAULT 0,
  `birth_date` varchar(80) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `gender` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `user_role`, `email`, `reset_token`, `reset_expires`, `is_banned`, `birth_date`, `address`, `gender`) VALUES
(2, 'u2', '$2y$10$fjRCzs8GdYoqrRK4FsUMlebLtWdKCdWLdH.FtLETuHorXJj2jRLm6', 'player', 'u2@example.com', NULL, NULL, 0, NULL, NULL, NULL),
(3, 'u3', '$2y$10$fjRCzs8GdYoqrRK4FsUMlebLtWdKCdWLdH.FtLETuHorXJj2jRLm6', 'player', 'u3@example.com', NULL, NULL, 0, NULL, NULL, NULL),
(4, 'u4', '$2y$10$fjRCzs8GdYoqrRK4FsUMlebLtWdKCdWLdH.FtLETuHorXJj2jRLm6', 'player', 'u4@example.com', NULL, NULL, 0, NULL, NULL, NULL),
(5, 'u5', '$2y$10$fjRCzs8GdYoqrRK4FsUMlebLtWdKCdWLdH.FtLETuHorXJj2jRLm6', 'player', 'u5@example.com', NULL, NULL, 0, NULL, NULL, NULL),
(54, 'test6', '$2y$10$UQgaivC89GSTJ.1S5g0k0.ES8iOj4uZxcFRKwo.uUATuBDHlaQg/W', 'developer', 'test6@gmail.com', NULL, NULL, 0, '2001-02-13', 'asdasd', 'male'),
(55, 'test6', '$2y$10$zEd4e1euvbKeH96Lx6WyEuouRY/DtPxpD6cV.Uur6G30O7QIE5iRe', 'developer', 'test6@gmail.com', NULL, NULL, 0, '2001-02-13', 'asdasd', 'male'),
(56, 'test7', '$2y$10$dv2CwK/oTWiTWL.onpj6iOKKr8EHz2MmfgJ6wtV1WZDZq0lv39h6y', 'developer', 'test7@gmail.com', NULL, NULL, 0, '2001-02-13', 'asdasd', 'male'),
(57, 'test8', '$2y$10$5ber3NbQREvI.WNRed/rS.S4VQ9AlaFKEnJ7BKt6qgD0WEnNQ0W8G', 'developer', 'test8@gmail.com', NULL, NULL, 0, '2001-02-13', 'asdasd', 'male'),
(58, 'MohamedRayen.Chihi', '$2y$10$o4yd2Gu6oPVIb3BvCIUNyOXiPZFxG0vqf3lNxnEv2bu8vPHIPHo/y', 'admin', 'rchihi711@gmail.com', NULL, NULL, 0, '2000-12-12', 'aaaaaaaaaaa', 'male'),
(60, 'saif', '$2y$10$/5d/bCzyK7EJx837WNUqEO/DK8.KKDmIppDz6UQqeFyUD2QNpCtPe', 'player', 'saifamami251@gmail.com', NULL, NULL, 0, '2006-02-20', 'arienna', 'male'),
(61, 'med', '$2y$10$TwamJbTs8jlu.M67YlMynuavN0f.2T5vd7NZsEuqzFcrlBedkOOwS', 'admin', 'hbaieb@gmail.com', NULL, NULL, 0, '1999-05-07', 'aaaaaaaaaaaaaaa', 'male'),
(62, 'med12', '$2y$10$L9YZLFZ50auq5akT/q5.2eA/a2554hW073RXyeLf8D8e4T8GlicyK', 'player', 'med@gmail.com', NULL, NULL, 0, '1995-02-02', 'aaaaaaaaaaaaaaa', 'male'),
(63, 'ayman', '$2y$10$9nwjf60FjQ1cqsNz5K1kl.YwBJiUfRRKaeIwTyFIcHbGhfLGH3uPm', 'developer', 'aymanhamoda8@gmail.com', NULL, NULL, 0, '2001-03-01', 'tunis-radis', 'male'),
(64, 'aymanSuper', '$2y$10$PfQbiVBy2v0lSQ5DqezwNONJdq4QVneOu1bCyZun0YrH6PDpnqC8.', 'super_admin', 'ayman123hamoda@gmail.com', NULL, NULL, 0, '2005-01-31', 'tunis-radis', 'male'),
(65, 'ayman', '$2y$10$Y1zWJDpQAchDp6XKTNK0He/7pFoVESpBP788c1kdDf3sObAtMFq76', 'admin', 'ayman.hamoda981@gmail.com', NULL, NULL, 0, '2001-03-01', 'tunis-radis', 'male');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_id` (`post_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `company_members`
--
ALTER TABLE `company_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_member` (`company_id`,`user_id`),
  ADD KEY `fk_company_members_user` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_events_organizer` (`organizer_id`);

--
-- Indexes for table `event_participation`
--
ALTER TABLE `event_participation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_participation` (`user_id`,`event_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_event_id` (`event_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`game_id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_game` (`game`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `fk_feedback_user` (`user_id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`game_id`),
  ADD KEY `developer_id` (`developer_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_is_deleted` (`is_deleted`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `idx_is_read` (`is_read`);

--
-- Indexes for table `organizer`
--
ALTER TABLE `organizer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_organizer` (`user_id`);

--
-- Indexes for table `participations`
--
ALTER TABLE `participations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tournament_user` (`tournament_id`,`user_id`),
  ADD KEY `fk_part_user` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_archived` (`is_archived`);

--
-- Indexes for table `post_replies`
--
ALTER TABLE `post_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comment_id` (`comment_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `reactions`
--
ALTER TABLE `reactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_target` (`target`);

--
-- Indexes for table `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_feedback_id` (`feedback_id`),
  ADD KEY `idx_date` (`date`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_review` (`game_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reward_id` (`reward_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `company_members`
--
ALTER TABLE `company_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `event_participation`
--
ALTER TABLE `event_participation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `game_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `organizer`
--
ALTER TABLE `organizer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `participations`
--
ALTER TABLE `participations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `post_replies`
--
ALTER TABLE `post_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reactions`
--
ALTER TABLE `reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `replies`
--
ALTER TABLE `replies`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company`
--
ALTER TABLE `company`
  ADD CONSTRAINT `company_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_members`
--
ALTER TABLE `company_members`
  ADD CONSTRAINT `fk_company_members_company` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_company_members_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `organizer` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_participation`
--
ALTER TABLE `event_participation`
  ADD CONSTRAINT `event_participation_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `event_participation_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `games`
--
ALTER TABLE `games`
  ADD CONSTRAINT `games_ibfk_1` FOREIGN KEY (`developer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `games_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `organizer`
--
ALTER TABLE `organizer`
  ADD CONSTRAINT `fk_organizer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `participations`
--
ALTER TABLE `participations`
  ADD CONSTRAINT `fk_part_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_part_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_replies`
--
ALTER TABLE `post_replies`
  ADD CONSTRAINT `fk_post_replies_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_post_replies_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `fk_reply_feedback` FOREIGN KEY (`feedback_id`) REFERENCES `feedback` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD CONSTRAINT `fk_tournaments_rewards` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
