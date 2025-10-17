# Nexus One Migrations

The web portal automatically provisions the `site_news` table when accessed.
If you prefer running migrations manually, execute the following SQL on the
same database that powers your Devnexus server:

```sql
CREATE TABLE IF NOT EXISTS `site_news` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `body` text NOT NULL,
  `created_at` int unsigned NOT NULL,
  `author_id` int NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `site_news_author_fk` FOREIGN KEY (`author_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
