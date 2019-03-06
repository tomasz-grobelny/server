<?php
require_once __DIR__ . '/lib/versioncheck.php';
require_once __DIR__ . '/lib/base.php';
require_once __DIR__ . '/Output2.php';
require_once __DIR__ . '/Internal2.php';

use OCP\AppFramework\Http\IOutput;
use OC\Server;
use OC\Session\CryptoWrapper;
use OCP\IUserSession;
use OC\AppFramework\Http\Request;

\OC::$server->registerService(IOutput::class, function(){
	return new OC\AppFramework\Http\Output2();
});

function registerRequest(Psr\Http\Message\ServerRequestInterface $request) {
	\OC::$server->registerService('UserId', function ($c) {
		return $c->query(IUserSession::class)->getSession()->get('user_id');
	});
	\OC::$server->registerAlias('userId', 'UserId');
	\OC::$server->registerService(\OCP\IRequest::class, function ($c) use ($request) {
		$urlParams = [];
		$stream = 'php://input';
		$server = $request->getServerParams();
		$server['SCRIPT_NAME'] = "/index.php";
		$server['SERVER_PROTOCOL'] = 'HTTP/1.1';
		return new Request(
			[
				'get' => $request->getQueryParams(),
				'post' => $_POST,
				'files' => $_FILES,
				'server' => $server,
				'env' => $_ENV,
				'cookies' => $request->getCookieParams(),
				'method' => $request->getMethod(),
				'urlParams' => $urlParams,
			],
			\OC::$server->getSecureRandom(),
			\OC::$server->getConfig(),
			\OC::$server->getCsrfTokenManager(),
			$stream
		);
	});
	\OC::$server->registerAlias('Request', \OCP\IRequest::class);
	\OC::$server->registerService('CryptoWrapper', function (Server $c) use($request) {
		// FIXME: Instantiiated here due to cyclic dependency
		$urlParams = [];
		$stream = 'php://input';
		$server = $request->getServerParams();
		$server['SCRIPT_NAME'] = "/index.php";
		$server['SERVER_PROTOCOL'] = 'HTTP/1.1';
		$p1=[
				'get' => $request->getQueryParams(),
				'post' => $_POST,
				'files' => $_FILES,
				'server' => $server,
				'env' => $_ENV,
				'cookies' => $request->getCookieParams(),
				'method' => $request->getMethod(),
				'urlParams' => $urlParams,
			];
			$p2=\OC::$server->getSecureRandom();
			$p3=\OC::$server->getConfig();
			$p4=\OC::$server->getCsrfTokenManager();
		$request2 = new Request($p1, $p2, $p3, $p4, $stream);
		return new CryptoWrapper(
			\OC::$server->getConfig(),
			\OC::$server->getCrypto(),
			\OC::$server->getSecureRandom(),
			$request2
			);
	});
}

try {
	$loop = React\EventLoop\Factory::create();

	$server = new React\Http\Server(function (Psr\Http\Message\ServerRequestInterface $request) {
		try {
			$start = microtime(true);
			registerRequest($request);
			$sessionName = OC_Util::getInstanceId();

			$useCustomSession = false;
			$session = \OC::$server->getSession();
			OC_Hook::emit('OC', 'initSession', array('session' => &$session, 'sessionName' => &$sessionName, 'useCustomSession' => &$useCustomSession));
			if (!$useCustomSession) {
				// set the session name to the instance id - which is unique
				$session = new Internal2($sessionName);
			}
			$cryptoWrapper = \OC::$server->getSessionCryptoWrapper();
			$session = $cryptoWrapper->wrapSession($session);
			\OC::$server->setSession($session);
			OC_Util::setupFS();
			\OC\Files\Filesystem::reset();
			OC_Util::resetupFS();

			\OC::$server->getRouter()->match('/core/preview');
			$io = \OC::$server->query(\OCP\AppFramework\Http\IOutput::class);
			echo 'serving time: ' . (microtime(true)-$start) . PHP_EOL;
			return new React\Http\Response(
				200,
				array('Content-Type' => 'image/jpeg'),
				$io->getOutput()
			);
		} catch(\Exception $ex) {
			echo 'error: ' . $ex->getMessage() . "\n";
		}
	});

	$socket = new React\Socket\Server(8080, $loop);
	$server->listen($socket);
	echo "Server running at http://0.0.0.0:8080\n";
	$loop->run();
}
catch(\Exception $ex)
{
	echo 'error: ' . $ex->getMessage() . "\n";
}
