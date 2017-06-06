<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * calendar notes
 */
class Version20170606090000 extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface|null */
    private $container;

    /**
     * @param ContainerInterface|null $container
     */
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
        $this->addSql('CREATE TABLE `'.$prefix.'calendar_note` (id INT UNSIGNED AUTO_INCREMENT NOT NULL, category_id INT UNSIGNED NOT NULL, account_id INT UNSIGNED NOT NULL, note TINYTEXT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, INDEX IDX_5BF96DB12469DE2 (category_id), INDEX IDX_5BF96DBF75A974A (accountid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `'.$prefix.'calendar_note_category` (id INT UNSIGNED AUTO_INCREMENT NOT NULL, account_id INT UNSIGNED NOT NULL, internal_id tinyint NULL, name VARCHAR(50) NOT NULL, color CHAR(6) NOT NULL, privacy tinyint unsigned NOT NULL DEFAULT 1, INDEX IDX_D1C190C9F75A974A (accountid), UNIQUE INDEX unique_internal_id (accountid, internal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `'.$prefix.'calendar_note` ADD CONSTRAINT FK_5BF96DB12469DE2 FOREIGN KEY (category_id) REFERENCES `'.$prefix.'calendar_note_category` (id)');
        $this->addSql('ALTER TABLE `'.$prefix.'calendar_note` ADD CONSTRAINT FK_5BF96DBF75A974A FOREIGN KEY (account_id) REFERENCES `'.$prefix.'account` (id)');
        $this->addSql('ALTER TABLE `'.$prefix.'calendar_note_category` ADD CONSTRAINT FK_D1C190C9F75A974A FOREIGN KEY (account_id) REFERENCES `'.$prefix.'account` (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $prefix = $this->container->getParameter('database_prefix');
        $this->addSql('DROP TABLE IF EXISTS `'.$prefix.'calendar_note`');
        $this->addSql('DROP TABLE IF EXISTS `'.$prefix.'calendar_note_category`');

    }
}
