
-- --------------------------------------------------------

--
-- Структура таблицы `buhbase`
--

CREATE TABLE `buhbase` (
  `id` int(11) NOT NULL,
  `user_name` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `chat_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `consumption` float DEFAULT '0',
  `income` float DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `buhbase`
--
ALTER TABLE `buhbase`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `buhbase`
--
ALTER TABLE `buhbase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
