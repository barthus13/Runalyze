<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20170730165002 extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface|null */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');

        $this->addSql('CREATE TABLE `'.$prefix.'client` (id INT AUTO_INCREMENT NOT NULL, random_id VARCHAR(255) NOT NULL, redirect_uris LONGTEXT NOT NULL, secret VARCHAR(255) NOT NULL, allowed_grant_types LONGTEXT NOT NULL, name VARCHAR(50) NOT NULL, description TEXT DEFAULT NULL, mail VARCHAR(100) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `'.$prefix.'access_token` (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user INT UNSIGNED NOT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_F06DFA2A5F37A13B (token), INDEX IDX_F06DFA2A19EB6921 (client_id), INDEX IDX_F06DFA2A8D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `'.$prefix.'refresh_token` (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT UNSIGNED DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_5FDBCE0E5F37A13B (token), INDEX IDX_5FDBCE0E19EB6921 (client_id), INDEX IDX_5FDBCE0EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `'.$prefix.'auth_code` (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user INT UNSIGNED NOT NULL, token VARCHAR(255) NOT NULL, redirect_uri LONGTEXT NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_5A29D59E5F37A13B (token), INDEX IDX_5A29D59E19EB6921 (client_id), INDEX IDX_5A29D59E8D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `'.$prefix.'access_token` ADD CONSTRAINT FK_F06DFA2A19EB6921 FOREIGN KEY (client_id) REFERENCES `'.$prefix.'client` (id)');
        $this->addSql('ALTER TABLE `'.$prefix.'access_token` ADD CONSTRAINT FK_F06DFA2A8D93D649 FOREIGN KEY (user) REFERENCES `'.$prefix.'account` (id)');
        $this->addSql('ALTER TABLE `'.$prefix.'refresh_token` ADD CONSTRAINT FK_5FDBCE0E19EB6921 FOREIGN KEY (client_id) REFERENCES `'.$prefix.'client` (id)');
        $this->addSql('ALTER TABLE `'.$prefix.'refresh_token` ADD CONSTRAINT FK_5FDBCE0EA76ED395 FOREIGN KEY (user_id) REFERENCES `'.$prefix.'account` (id)');
        $this->addSql('ALTER TABLE `'.$prefix.'auth_code` ADD CONSTRAINT FK_5A29D59E19EB6921 FOREIGN KEY (client_id) REFERENCES `'.$prefix.'client` (id)');
        $this->addSql('ALTER TABLE `'.$prefix.'auth_code` ADD CONSTRAINT FK_5A29D59E8D93D649 FOREIGN KEY (user) REFERENCES `'.$prefix.'account` (id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');
        $this->addSql('DROP TABLE IF EXISTS `'.$prefix.'client`');
        $this->addSql('DROP TABLE IF EXISTS `'.$prefix.'access_token`');
        $this->addSql('DROP TABLE IF EXISTS `'.$prefix.'refresh_token`');
        $this->addSql('DROP TABLE IF EXISTS `'.$prefix.'auth_code`');


    }
}
