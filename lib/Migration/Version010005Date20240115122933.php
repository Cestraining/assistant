<?php

// SPDX-FileCopyrightText: Sami Finnilä <sami.finnila@nextcloud.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace OCA\TpAssistant\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version010005Date20240115122933 extends SimpleMigrationStep {
	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return void
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();
		$schemaChanged = false;

		if ($schema->hasTable('assistant_stt_transcripts')) {
			// Storing transcripts has been moved to the assistant meta task wrapper
			$schemaChanged = true;
			$table = $schema->getTable('assistant_stt_transcripts');
			$table->dropIndex('assistant_stt_transcript_user');
			$table->dropIndex('assistant_stt_transcript_la');
			$schema->dropTable('assistant_stt_transcripts');
		}

		if (!$schema->hasTable('assistant_text_tasks')) {
			$schemaChanged = true;
			$table = $schema->createTable('assistant_text_tasks');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('app_id', Types::STRING, [
				'notnull' => true,
			]);
			$table->addColumn('inputs', Types::TEXT, [
				'notnull' => false,
			]);
            $table->addColumn('output', Types::TEXT, [
                'notnull' => false,
            ]);
            $table->addColumn('ocp_task_id', Types::BIGINT, [
                'notnull' => false,
            ]);
			$table->addColumn('timestamp', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('task_type', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('status', Types::INTEGER, [
				'notnull' => true,
				'default' => 0, // 0 = Unknown
			]);
			$table->addColumn('modality', Types::INTEGER, [
				'notnull' => false,
			]);
			$table->addColumn('indentifer', Types::STRING, [
				'notnull' => false,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'assistant_t_tasks_uid');
			$table->addIndex(['ocp_task_id','modality'], 'assistant_t_task_id_modality');
		}

		return $schemaChanged ? $schema : null;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return void
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}
}