<?php
//declare(strict_types=1);
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author cetra3 <peter@parashift.com.au>
 * @author Christoph Wurst <christoph@owncloud.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <robin@icewind.nl>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Victor Dubiniuk <dubiniuk@owncloud.com>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

use OC\Authentication\Exceptions\InvalidTokenException;
use OC\Authentication\Token\IProvider;
use OC\SystemConfig;
use OCP\IConfig;
use OCP\Session\Exceptions\SessionNotAvailableException;
use OC\Session\Session;

/**
 * Class Internal
 *
 * wrap php's internal session handling into the Session interface
 *
 * @package OC\Session
 */
class Internal2 extends Session {
	private $request;
	private $sessionName;

	/**
	 * @param string $name
	 * @throws \Exception
	 */
	public function __construct(string $name) {
		$this->sessionName = $name;
		$this->request = \OC::$server->getRequest();
	}

	private function read() {
		$sessionId = $this->request->getCookie($this->sessionName);
		$filename = "/tmp/sess_" . $sessionId;
		if (file_exists($filename)) {
			$arr = [];
			$contents = file_get_contents($filename);
			$lines = explode("\n", $contents);
			foreach($lines as $line) {
				$pos = strpos($line, "|");
				$key = substr($line, 0, $pos);
				$value = substr($line, $pos + 1);
				$arr[$key] = unserialize($value);
			}
			return $arr;
		} else {
			return [];
		}
	}

	private function save($data) {
//		$sessionId = $this->request->getCookie($this->sessionName);
//		$filename = "/var/www/html/data/session/" . $sessionId;
//		file_put_contents($filename, serialize($data));
	}

	private function deleteFile() {
//		$sessionId = $this->request->getCookie($this->sessionName);
//		$filename = "/var/www/html/data/session/" . $sessionId;
//		unlink($filename);
	}

	/**
	 * @param string $key
	 * @param integer $value
	 */
	public function set(string $key, $value) {
		$this->validateSession();
		$data = self::read();
		$data[$key] = $value;
		self::save($data);
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key) {
		if (!$this->exists($key)) {
			return null;
		}
		$data = self::read();
		return $data[$key];
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function exists(string $key): bool {
		$data = self::read();
		return isset($data[$key]);
	}

	/**
	 * @param string $key
	 */
	public function remove(string $key) {
		$data = self::read();
		if (isset($data[$key])) {
			unset($data[$key]);
			self::save($data);
		}
	}

	public function clear() {
		$this->deleteFile();
		$this->regenerateId();
	}

	public function close() {
		parent::close();
	}

	/**
	 * Wrapper around session_regenerate_id
	 *
	 * @param bool $deleteOldSession Whether to delete the old associated session file or not.
	 * @param bool $updateToken Wheater to update the associated auth token
	 * @return void
	 */
	public function regenerateId(bool $deleteOldSession = true, bool $updateToken = false) {
		$oldId = null;

		if ($updateToken) {
			// Get the old id to update the token
			try {
				$oldId = $this->getId();
			} catch (SessionNotAvailableException $e) {
				// We can't update a token if there is no previous id
				$updateToken = false;
			}
		}

		try {
			@session_regenerate_id($deleteOldSession);
		} catch (\Error $e) {
			$this->trapError($e->getCode(), $e->getMessage());
		}

		if ($updateToken) {
			// Get the new id to update the token
			$newId = $this->getId();

			/** @var IProvider $tokenProvider */
			$tokenProvider = \OC::$server->query(IProvider::class);

			try {
				$tokenProvider->renewSessionToken($oldId, $newId);
			} catch (InvalidTokenException $e) {
				// Just ignore
			}
		}
	}

	/**
	 * Wrapper around session_id
	 *
	 * @return string
	 * @throws SessionNotAvailableException
	 * @since 9.1.0
	 */
	public function getId(): string {
		$sessionId = $this->request->getCookie($this->sessionName);
		if ($sessionId === '' || $sessionId === null) {
			throw new SessionNotAvailableException();
		}
		return $sessionId;
	}

	/**
	 * @throws \Exception
	 */
	public function reopen() {
		throw new \Exception('The session cannot be reopened - reopen() is ony to be used in unit testing.');
	}

	/**
	 * @param int $errorNumber
	 * @param string $errorString
	 * @throws \ErrorException
	 */
	public function trapError(int $errorNumber, string $errorString) {
		throw new \ErrorException($errorString);
	}

	/**
	 * @throws \Exception
	 */
	private function validateSession() {
		if ($this->sessionClosed) {
			throw new SessionNotAvailableException('Session has been closed - no further changes to the session are allowed');
		}
	}
}
