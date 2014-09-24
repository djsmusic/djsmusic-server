INSERT INTO `albums` (`id`, `usid`, `picid`, `src`, `name`, `description`, `timestamp`, `downloads`) VALUES
(1, 1, 1, '', 'Demo Album', 'Album description', 1242254820, 10);

INSERT INTO `users` (`id`, `user`, `pass`, `email`, `gender`, `state`, `rname`, `birth`, `jtime`, `ltime`, `picid`, `ip`, `city`, `region`, `country`, `interests`, `occupation`, `web`, `views`, `rev`, `level`, `featured`, `type`) VALUES
(1, 'Admin', '01394cd0ae9c2a1f4d06740c41b7607d1292888a', 'test@travis-ci.com', 1, 'Testing', 'Admin Account', 491979600, 1226165428, 1391454144, 1, '127.0.0.1', 'Oviedo', 'Asturias', 'es', 'DJs Music', 'Admin', 'http://djs-music.com', 1, '1', 11, 0, 1);

INSERT INTO `music` (`id`, `usid`, `albumid`, `title`, `src`, `duration`, `bitrate`, `size`, `genres`, `extra`, `download`, `r_users`, `r_total`, `timestamp`, `listens`, `downloads`, `contest`, `votes`, `rev`) VALUES
(1, 1, 1, 'Demo song', '1-1-bKzlLSdYiN.mp3', 208, 197594, 5146624, 'dance ', 'Song description', 1, 57, 229, 1236747600, 108433, 46613, 0, 0, '2');