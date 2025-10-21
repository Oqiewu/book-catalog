<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%subscriptions}}`.
 */
class m250000_000004_create_subscriptions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%subscriptions}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'email' => $this->string(255)->null(),
            'phone' => $this->string(20)->null(),
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-subscriptions-author_id',
            '{{%subscriptions}}',
            'author_id',
            '{{%authors}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex(
            'idx-subscriptions-author_id',
            '{{%subscriptions}}',
            'author_id'
        );

        $this->createIndex(
            'idx-subscriptions-email',
            '{{%subscriptions}}',
            'email'
        );

        $this->createIndex(
            'idx-subscriptions-phone',
            '{{%subscriptions}}',
            'phone'
        );

        // Уникальный индекс для комбинации автор + email или автор + phone
        $this->createIndex(
            'idx-subscriptions-unique-email',
            '{{%subscriptions}}',
            ['author_id', 'email'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-subscriptions-author_id', '{{%subscriptions}}');
        $this->dropTable('{{%subscriptions}}');
    }
}
