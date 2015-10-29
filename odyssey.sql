-- phpMyAdmin SQL Dump
-- version 4.2.12deb2
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 27-10-2015 a las 14:04:59
-- Versión del servidor: 5.5.44-0+deb8u1
-- Versión de PHP: 5.6.13-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `odyssey`
--
CREATE DATABASE odyssey;
USE odyssey;
-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `7_view`
--
CREATE TABLE IF NOT EXISTS `7_view` (
`NAME` varchar(25)
,`ARTIST` varchar(20)
,`ALBUM` varchar(25)
,`YEAR` int(4)
,`SIZE` int(11)
,`LYRICS` text
);
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `authentication`
--

CREATE TABLE IF NOT EXISTS `authentication` (
`ID` int(11) NOT NULL,
  `USERNAME` varchar(20) NOT NULL,
  `PASSWORD` varchar(32) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=ascii;

--
-- Volcado de datos para la tabla `authentication`
--

INSERT INTO `authentication` (`ID`, `USERNAME`, `PASSWORD`) VALUES
(7, 'siul34', 'd6a5f78ca3706d6245262454a9a090f2');

--
-- Disparadores `authentication`
--
DELIMITER //
CREATE TRIGGER `agregar_meta_usuario` AFTER INSERT ON `authentication`
 FOR EACH ROW BEGIN
	INSERT INTO users (ID) VALUES (NEW.ID);
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comments`
--

CREATE TABLE IF NOT EXISTS `comments` (
`ID` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `song_id` int(11) NOT NULL,
  `comment_id` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Dislikes`
--

CREATE TABLE IF NOT EXISTS `Dislikes` (
`ID` int(11) NOT NULL,
  `song_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `friends_relation`
--

CREATE TABLE IF NOT EXISTS `friends_relation` (
  `friends_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

--
-- Disparadores `friends_relation`
--
DELIMITER //
CREATE TRIGGER `check_ids` BEFORE INSERT ON `friends_relation`
 FOR EACH ROW BEGIN 
	IF NEW.user_id = NEW.friends_id THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'A user cannot be friend of him/herself';
    END IF;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Likes`
--

CREATE TABLE IF NOT EXISTS `Likes` (
`ID` int(11) NOT NULL,
  `song_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `songs`
--

CREATE TABLE IF NOT EXISTS `songs` (
`ID` int(11) NOT NULL,
  `NAME` varchar(25) DEFAULT NULL,
  `ALBUM` varchar(25) DEFAULT NULL,
  `YEAR` int(4) DEFAULT NULL,
  `ARTIST` varchar(20) DEFAULT NULL,
  `LYRICS` text,
  `SIZE` int(11) NOT NULL,
  `OWNER` int(11) NOT NULL,
  `TIMESTAMP` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

--
-- Disparadores `songs`
--
DELIMITER //
CREATE TRIGGER `insert_versions` BEFORE UPDATE ON `songs`
 FOR EACH ROW BEGIN
	INSERT INTO songs_versions 
    (ID_SONGS, NAME, ALBUM, YEAR, ARTIST, LYRICS, SIZE, OWNER, TIMESTAMP)
    SELECT * 
    FROM songs 
    WHERE OLD.ID = NEW.ID;
END
//
DELIMITER ;
DELIMITER //
CREATE TRIGGER `relation_user_song` AFTER INSERT ON `songs`
 FOR EACH ROW BEGIN
	INSERT INTO users_songs
    (users_songs.id_user,users_songs.id_song )
    VALUES
    (NEW.OWNER, NEW.ID);
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `songs_versions`
--

CREATE TABLE IF NOT EXISTS `songs_versions` (
`ID_SONGS_VERSIONS` int(11) NOT NULL,
  `ID_SONGS` int(11) NOT NULL,
  `NAME` varchar(25) DEFAULT NULL,
  `ALBUM` varchar(25) DEFAULT NULL,
  `YEAR` int(4) DEFAULT NULL,
  `ARTIST` varchar(20) DEFAULT NULL,
  `LYRICS` text,
  `SIZE` int(11) NOT NULL,
  `OWNER` int(11) NOT NULL,
  `TIMESTAMP` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE IF NOT EXISTS `users` (
`ID` int(11) NOT NULL,
  `NAME` varchar(15) DEFAULT NULL,
  `LASTNAME` varchar(20) DEFAULT NULL,
  `GENRE` varchar(20) DEFAULT NULL,
  `POPULARITY` int(11) DEFAULT NULL,
  `STATUS` char(1) DEFAULT NULL,
  `PHOTO` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=ascii;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`ID`, `NAME`, `LASTNAME`, `GENRE`, `POPULARITY`, `STATUS`, `PHOTO`) VALUES
(7, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_songs`
--

CREATE TABLE IF NOT EXISTS `users_songs` (
  `id_user` int(11) NOT NULL,
  `id_song` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- --------------------------------------------------------

--
-- Estructura para la vista `7_view`
--
DROP TABLE IF EXISTS `7_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `7_view` AS select `songs`.`NAME` AS `NAME`,`songs`.`ARTIST` AS `ARTIST`,`songs`.`ALBUM` AS `ALBUM`,`songs`.`YEAR` AS `YEAR`,`songs`.`SIZE` AS `SIZE`,`songs`.`LYRICS` AS `LYRICS` from `songs` where (`songs`.`OWNER` = 7);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `authentication`
--
ALTER TABLE `authentication`
 ADD PRIMARY KEY (`ID`), ADD UNIQUE KEY `USERNAME_2` (`USERNAME`), ADD KEY `USERNAME` (`USERNAME`);

--
-- Indices de la tabla `comments`
--
ALTER TABLE `comments`
 ADD PRIMARY KEY (`ID`,`user_id`,`song_id`), ADD UNIQUE KEY `comment_id` (`comment_id`);

--
-- Indices de la tabla `Dislikes`
--
ALTER TABLE `Dislikes`
 ADD PRIMARY KEY (`ID`,`song_id`,`user_id`), ADD KEY `dislikes_userid_fk` (`user_id`), ADD KEY `dislikes_songid_fk` (`song_id`);

--
-- Indices de la tabla `friends_relation`
--
ALTER TABLE `friends_relation`
 ADD PRIMARY KEY (`friends_id`,`user_id`), ADD KEY `friends_relation_ibfk_2` (`user_id`);

--
-- Indices de la tabla `Likes`
--
ALTER TABLE `Likes`
 ADD PRIMARY KEY (`ID`,`song_id`,`user_id`), ADD KEY `likes_userid_fk` (`user_id`), ADD KEY `likes_songid_fk` (`song_id`);

--
-- Indices de la tabla `songs`
--
ALTER TABLE `songs`
 ADD PRIMARY KEY (`ID`), ADD UNIQUE KEY `artist_index` (`ARTIST`), ADD UNIQUE KEY `NAME_2` (`NAME`), ADD KEY `name_index` (`NAME`), ADD KEY `lyrics_index` (`LYRICS`(255)), ADD KEY `album_index` (`ALBUM`), ADD KEY `year_index` (`YEAR`), ADD KEY `size` (`SIZE`), ADD KEY `ownerIndex` (`OWNER`), ADD KEY `TIMESTAMP` (`TIMESTAMP`), ADD KEY `NAME` (`NAME`);

--
-- Indices de la tabla `songs_versions`
--
ALTER TABLE `songs_versions`
 ADD PRIMARY KEY (`ID_SONGS_VERSIONS`,`ID_SONGS`), ADD KEY `size` (`SIZE`), ADD KEY `songs_versions_songid_fk` (`ID_SONGS`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `users_songs`
--
ALTER TABLE `users_songs`
 ADD PRIMARY KEY (`id_user`,`id_song`), ADD KEY `users_songs_songid_fk` (`id_song`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `authentication`
--
ALTER TABLE `authentication`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT de la tabla `comments`
--
ALTER TABLE `comments`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `Dislikes`
--
ALTER TABLE `Dislikes`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `Likes`
--
ALTER TABLE `Likes`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `songs`
--
ALTER TABLE `songs`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `songs_versions`
--
ALTER TABLE `songs_versions`
MODIFY `ID_SONGS_VERSIONS` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `Dislikes`
--
ALTER TABLE `Dislikes`
ADD CONSTRAINT `dislikes_userid_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `friends_relation`
--
ALTER TABLE `friends_relation`
ADD CONSTRAINT `friends_relation_ibfk_1` FOREIGN KEY (`friends_id`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `friends_relation_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `Likes`
--
ALTER TABLE `Likes`
ADD CONSTRAINT `likes_userid_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `songs`
--
ALTER TABLE `songs`
ADD CONSTRAINT `songs_ibfk_1` FOREIGN KEY (`OWNER`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `authentication` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `users_songs`
--
ALTER TABLE `users_songs`
ADD CONSTRAINT `users_songs_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
