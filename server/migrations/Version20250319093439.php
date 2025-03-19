<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250319093439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invitation (invited_user_id INT NOT NULL, wishlist_id INT NOT NULL, status BOOLEAN NOT NULL, PRIMARY KEY(invited_user_id, wishlist_id))');
        $this->addSql('CREATE INDEX IDX_F11D61A2C58DAD6E ON invitation (invited_user_id)');
        $this->addSql('CREATE INDEX IDX_F11D61A2FB8E54CD ON invitation (wishlist_id)');
        $this->addSql('CREATE TABLE item (id SERIAL NOT NULL, wishlist_id INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1F1B251EFB8E54CD ON item (wishlist_id)');
        $this->addSql('CREATE TABLE purchase (item_id INT NOT NULL, buyer_id INT NOT NULL, purchase_proof VARCHAR(255) NOT NULL, congratulatory_text VARCHAR(255) NOT NULL, PRIMARY KEY(item_id))');
        $this->addSql('CREATE INDEX IDX_6117D13B6C755722 ON purchase (buyer_id)');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, is_locked BOOLEAN NOT NULL, is_admin BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE wishlist (id SERIAL NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, deadline DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9CE12A317E3C61F9 ON wishlist (owner_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2C58DAD6E FOREIGN KEY (invited_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2FB8E54CD FOREIGN KEY (wishlist_id) REFERENCES wishlist (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EFB8E54CD FOREIGN KEY (wishlist_id) REFERENCES wishlist (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B126F525E FOREIGN KEY (item_id) REFERENCES item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B6C755722 FOREIGN KEY (buyer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE wishlist ADD CONSTRAINT FK_9CE12A317E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE invitation DROP CONSTRAINT FK_F11D61A2C58DAD6E');
        $this->addSql('ALTER TABLE invitation DROP CONSTRAINT FK_F11D61A2FB8E54CD');
        $this->addSql('ALTER TABLE item DROP CONSTRAINT FK_1F1B251EFB8E54CD');
        $this->addSql('ALTER TABLE purchase DROP CONSTRAINT FK_6117D13B126F525E');
        $this->addSql('ALTER TABLE purchase DROP CONSTRAINT FK_6117D13B6C755722');
        $this->addSql('ALTER TABLE wishlist DROP CONSTRAINT FK_9CE12A317E3C61F9');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE purchase');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE wishlist');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
