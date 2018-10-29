<?php
/**
 * @copyright Copyright (c) 2018 John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @author John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OC\Repair\NC13;


use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;

class SetVcardDatabaseUID implements IRepairStep {
	const MAX_ROWS = 1000;

	/** @var IDBConnection */
	private $connection;
	private $updateQuery;

	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
	}


	public function getName() {
		return 'Extract the vcard uid and store it in the db';
	}

	/**
	 * @return \Generator
	 * @suppress SqlInjectionChecker
	 */
	private function getInvalidEntries() {
		$builder = $this->connection->getQueryBuilder();

		$builder->select('id', 'carddata')
			->from('cards')
			->where($builder->expr()->isNull('uid'))
			->setMaxResults(self::MAX_ROWS);

		do {
			$result = $builder->execute();
			$rows = $result->fetchAll();
			foreach ($rows as $row) {
				yield $row;
			}
			$result->closeCursor();
		} while (count($rows) > 0);
	}

	private function getUid($carddata) {
		preg_match('/^UID:(.*)$/m', $carddata, $matches);
		if (count($matches > 1)) {
			return $matches[1][0];
		}
		return false;
	}

	/**
	 * @param int $id
	 * @param string $uid
	 */
	private function update($id, $uid) {
		if (!$this->updateQuery) {
			$builder = $this->connection->getQueryBuilder();

			$this->updateQuery = $builder->update('cards')
				->set('uid', $builder->createParameter('uid'))
				->where($builder->expr()->eq('id', $builder->createParameter('id')));
		}

		$this->updateQuery->setParameter('id', $id);
		$this->updateQuery->setParameter('uid', $uid);

		$this->updateQuery->execute();
	}

	private function repair() {
		$this->connection->beginTransaction();
		$entries = $this->getInvalidEntries();
		$count = 0;
		foreach ($entries as $entry) {
			$count++;
			$uid = $this->getUid($entry['id']);
			if ($uid !== false) {
				$this->update($entry['id'], $uid);
			}
		}
		$this->connection->commit();
		return $count;
	}

	private function shouldRun() {
		$versionFromBeforeUpdate = $this->config->getSystemValue('version', '0.0.0');

		// was added to 15.0.0.0
		return version_compare($versionFromBeforeUpdate, '15.0.0.0', '<=');
	}

	public function run(IOutput $output) {
		if ($this->shouldRun()) {
			$count = $this->repair();

			$output->info('Fixed ' . $count . ' vcards');
		}
	}
}
