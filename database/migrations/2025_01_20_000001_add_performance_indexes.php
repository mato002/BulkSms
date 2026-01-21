<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Indexes for messages table
        Schema::table('messages', function (Blueprint $table) {
            if (!$this->indexExists('messages', 'messages_client_id_index')) {
                $table->index('client_id', 'messages_client_id_index');
            }
            if (!$this->indexExists('messages', 'messages_status_index')) {
                $table->index('status', 'messages_status_index');
            }
            if (!$this->indexExists('messages', 'messages_channel_index')) {
                $table->index('channel', 'messages_channel_index');
            }
            if (!$this->indexExists('messages', 'messages_created_at_index')) {
                $table->index('created_at', 'messages_created_at_index');
            }
            // Composite index for common queries
            if (!$this->indexExists('messages', 'messages_client_status_index')) {
                $table->index(['client_id', 'status'], 'messages_client_status_index');
            }
            if (!$this->indexExists('messages', 'messages_client_created_index')) {
                $table->index(['client_id', 'created_at'], 'messages_client_created_index');
            }
        });

        // Indexes for contacts table
        Schema::table('contacts', function (Blueprint $table) {
            if (!$this->indexExists('contacts', 'contacts_client_id_index')) {
                $table->index('client_id', 'contacts_client_id_index');
            }
            if (!$this->indexExists('contacts', 'contacts_contact_index')) {
                $table->index('contact', 'contacts_contact_index');
            }
            // Composite for search queries
            if (!$this->indexExists('contacts', 'contacts_client_contact_index')) {
                $table->index(['client_id', 'contact'], 'contacts_client_contact_index');
            }
        });

        // Indexes for campaigns table
        Schema::table('campaigns', function (Blueprint $table) {
            if (!$this->indexExists('campaigns', 'campaigns_client_id_index')) {
                $table->index('client_id', 'campaigns_client_id_index');
            }
            if (!$this->indexExists('campaigns', 'campaigns_status_index')) {
                $table->index('status', 'campaigns_status_index');
            }
            if (!$this->indexExists('campaigns', 'campaigns_scheduled_at_index')) {
                $table->index('scheduled_at', 'campaigns_scheduled_at_index');
            }
        });

        // Indexes for api_logs table (only if table exists)
        if (Schema::hasTable('api_logs')) {
            Schema::table('api_logs', function (Blueprint $table) {
                if (!$this->indexExists('api_logs', 'api_logs_client_id_index')) {
                    $table->index('client_id', 'api_logs_client_id_index');
                }
                if (!$this->indexExists('api_logs', 'api_logs_success_index')) {
                    $table->index('success', 'api_logs_success_index');
                }
                if (!$this->indexExists('api_logs', 'api_logs_created_at_index')) {
                    $table->index('created_at', 'api_logs_created_at_index');
                }
                // Composite for filtering
                if (!$this->indexExists('api_logs', 'api_logs_client_created_index')) {
                    $table->index(['client_id', 'created_at'], 'api_logs_client_created_index');
                }
            });
        }

        // Indexes for wallet_transactions table
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (!$this->indexExists('wallet_transactions', 'wallet_transactions_client_id_index')) {
                $table->index('client_id', 'wallet_transactions_client_id_index');
            }
            if (!$this->indexExists('wallet_transactions', 'wallet_transactions_status_index')) {
                $table->index('status', 'wallet_transactions_status_index');
            }
            if (!$this->indexExists('wallet_transactions', 'wallet_transactions_type_index')) {
                $table->index('type', 'wallet_transactions_type_index');
            }
            if (!$this->indexExists('wallet_transactions', 'wallet_transactions_created_at_index')) {
                $table->index('created_at', 'wallet_transactions_created_at_index');
            }
        });

        // Indexes for conversations table
        Schema::table('conversations', function (Blueprint $table) {
            if (!$this->indexExists('conversations', 'conversations_client_id_index')) {
                $table->index('client_id', 'conversations_client_id_index');
            }
            if (!$this->indexExists('conversations', 'conversations_contact_id_index')) {
                $table->index('contact_id', 'conversations_contact_id_index');
            }
            if (!$this->indexExists('conversations', 'conversations_last_message_at_index')) {
                $table->index('last_message_at', 'conversations_last_message_at_index');
            }
        });

        // Indexes for users table
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_client_id_index')) {
                $table->index('client_id', 'users_client_id_index');
            }
            if (!$this->indexExists('users', 'users_email_index')) {
                $table->index('email', 'users_email_index');
            }
        });

        // Indexes for channels table
        Schema::table('channels', function (Blueprint $table) {
            if (!$this->indexExists('channels', 'channels_client_id_index')) {
                $table->index('client_id', 'channels_client_id_index');
            }
            if (!$this->indexExists('channels', 'channels_name_index')) {
                $table->index('name', 'channels_name_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_client_id_index');
            $table->dropIndex('messages_status_index');
            $table->dropIndex('messages_channel_index');
            $table->dropIndex('messages_created_at_index');
            $table->dropIndex('messages_client_status_index');
            $table->dropIndex('messages_client_created_index');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('contacts_client_id_index');
            $table->dropIndex('contacts_contact_index');
            $table->dropIndex('contacts_client_contact_index');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropIndex('campaigns_client_id_index');
            $table->dropIndex('campaigns_status_index');
            $table->dropIndex('campaigns_scheduled_at_index');
        });

        if (Schema::hasTable('api_logs')) {
            Schema::table('api_logs', function (Blueprint $table) {
                $table->dropIndex('api_logs_client_id_index');
                $table->dropIndex('api_logs_success_index');
                $table->dropIndex('api_logs_created_at_index');
                $table->dropIndex('api_logs_client_created_index');
            });
        }

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropIndex('wallet_transactions_client_id_index');
            $table->dropIndex('wallet_transactions_status_index');
            $table->dropIndex('wallet_transactions_type_index');
            $table->dropIndex('wallet_transactions_created_at_index');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('conversations_client_id_index');
            $table->dropIndex('conversations_contact_id_index');
            $table->dropIndex('conversations_last_message_at_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_client_id_index');
            $table->dropIndex('users_email_index');
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->dropIndex('channels_client_id_index');
            $table->dropIndex('channels_name_index');
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();
        
        $result = $connection->select(
            "SELECT COUNT(*) as count FROM information_schema.statistics 
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$databaseName, $table, $indexName]
        );
        
        return $result[0]->count > 0;
    }
};




