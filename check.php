<?php
$cli = (php_sapi_name()=="cli");
if ($cli) {
	chdir(__DIR__);
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="content-type" />
		<title>Les RPM de Remi - Mirror check</title>
		<link href="fedora/17/remi/i386/repoview/layout/repostyle.css" type="text/css" rel="stylesheet" />
		<meta content="index,follow" name="robots" />
		<link rel="shortcut icon" href="/favicon.ico" />
</head>
<body>
	<div id="page">
		<div id="top">
			<h1><span><a href="/">Les RPM de Remi - Mirror check</a></span></h1>
		</div>
		<p id="prelude">
			<a href="http://blog.famillecollet.com/">Blog</a> | 
			<a href="http://forums.famillecollet.com/">Forums</a> | 
			<a href="/">Repository</a>
		</p>
		<div id="wrapper">
			<div id="main">
				<div id="content">
					<h2>Mirror repository metadata check</h2>
<?php
}
$repos = array(
	'enterprise/7'	=> 'Enterprise Linux 7',
	'enterprise/6'	=> 'Enterprise Linux 6',
	'enterprise/5'	=> 'Enterprise Linux 5',
	'fedora/22'	=> 'Fedora 22',
	'fedora/21' 	=> 'Fedora 21',
	'fedora/20' 	=> 'Fedora 20',
);
$subs = array(
	'remi',
	'test',
	'php56',
);
$archs = array(
	'i386',
	'x86_64',
);

$mirrors = array(
	'http://remi.mirrors.arminco.com/', 
	'http://remi.conetix.com.au/',
	'http://mirrors.neterra.net/remi/',
	'http://remi.xpg.com.br/',
	'http://mirror5.layerjet.com/remi/',
	'http://remi.schlundtech.de/',
	'http://mirror.cedia.org.ec/remi/',
	'http://mirror.uta.edu.ec/remi/',
	'http://iut-info.univ-reims.fr/remirpms/',
	'http://mirror.smartmedia.net.id/remi/',
	'http://ftp.arnes.si/mirrors/remi/',
	'http://mirrors.thzhost.com/remi/',
	'http://remi.check-update.co.uk/',
	'http://mirrors.mediatemple.net/remi/',
	'http://fr2.rpmfind.net/linux/remi/',
	'http://mirror.awanti.com/remi/',
	'http://mirrors.netix.net/remi/',
	'http://mirror.h1host.ru/remi/',
	'http://remi.mirrors.cu.be/',
	'http://mirror.innosol.asia/remi/',
	'http://mirror.neolabs.kz/remi/',
	'http://mirror.lablus.com/remi/',
	'http://mirror.veriteknik.net.tr/remi/',
	'https://remi.mirror.ate.info/',
);
$deprecated = array(
	'http://remi.kazukioishi.net/',
	'http://remirpm.mirror.gymkl.ch/',
	'http://remi.mirror.net.in/',
	'http://remi-mirror.dedipower.com/',
	'http://mirror.beyondhosting.net/Remi/',
	'http://mirrors.cicku.me/remi/',
	'http://remi.mirrors.hostinginnederland.nl/',
	'http://mirror.1000mbps.com/remi/',
	'http://mirrors.hustunique.com/remi/',
	'http://mirror.pw/remi/',
);
function getRepoTime($uri) {
	$xml = @simplexml_load_file($uri.'/repodata/repomd.xml');
	if ($xml && $xml->revision) {
		return intval($xml->revision);
	}
	return 0;
}
if (isset($_GET['mirror']) && isset($repos[$_GET['mirror']])) {
	$path = $_GET['mirror'];
} else {
	$path = 'enterprise/7';
}
if (isset($_GET['repo']) && in_array($_GET['repo'], $subs)) {
	$repo = $_GET['repo'];
} else {
	$repo = 'remi';
}
if (isset($_GET['arch']) && in_array($_GET['arch'], $archs)) {
	$arch = $_GET['arch'];
} else {
	$arch = 'x86_64';
}
$name = $repos[$path];
$full = "$path/$repo/$arch";

if (!$cli) {
	echo "<ul class='pkglist'>\n";
	foreach ($repos as $rpath => $rname) {
		if ($path == $rpath) {
			printf("<li><b>%s</b></li>\n", $rname);
		} else {
			printf("<li><a href='?mirror=%s'>%s</a></li>", $rpath, $rname);
		}
	}
	echo "</ul>\n";
}
$pids = array();
$ref = getRepoTime($full);
if ($ref) {
	printf(($cli ? "Check of %s (%s)\n" : "<h3>%s - %s</h3>\n"), $name, date('r', $ref));
	if (!$cli) echo "<ul class='pkglist'>\n";
	foreach ($mirrors as $mirror) {
		if ($cli) {
			$pid = pcntl_fork();
			if ($pid<0) {
				die("Can't fork\n");
			} else if ($pid) {
				$pids[$pid] = $pid;
			} else { // Child
 				$pids = array();
				$loc = getRepoTime($mirror.$full);
				if ($ref == $loc) {
					printf("%50.50s: Ok\n", $mirror);
				} else if ($loc) {
					printf("%50.50s: %s\n", $mirror, date('r', $loc));
				} else {
					printf("%50.50s: N/A\n", $mirror);
				}
				break;
			}
			continue;
		}
		flush();
		$host = parse_url($mirror, PHP_URL_HOST);
		printf("<li><a href='%s'>%s</a> ", $mirror, $host);
		$loc = getRepoTime($mirror.$full);
		if ($ref == $loc) {
			printf("<li><a href='%s'>%s</a> Ok</li>\n", $mirror, $mirror);
		} else if ($loc) {
			printf("<li><a href='%s'>%s</a> %s</li>\n", $mirror, $mirror, date('r', $loc));
		} else {
			printf("<li><a href='%s'>%s</a> N/A</li>\n", $mirror, $mirror);
		}
	}
	if ($cli) {
		while (count($pids)) {
			printf("Wait %d\r", count($pids));
			$pid = pcntl_wait($status);
			if ($pid<0) {
				die("Cound not wait\n");
				exit (1);
			} else {
				unset($pids[$pid]);
			}
		}
	} else {
		echo "</ul>\n";
	}
} else {
	printf("<h3>%s - not found</h3>\n", $name);
}

if (!$cli) {
?>
				</div>
			</div>
			<div id="sidebar">
				<h2>Other links</h2>
				<ul class="levbarlist">
					<li><a href="http://www.amazon.com/wishlist/1AFH00IXFY6M0" class="nlink" title="My Amazon.com Wishlist">WishList</a></li>
					<li><a href="http://www.amazon.fr/wishlist/33P6MW6KQC8GX"  class="nlink" title="Mes Envies cadeaux sur Amazon.fr">Envies cadeaux</a></li>
				</ul>

			</div>
		</div>
	        <hr style="clear:both;"/>
	</div>
	<div id="footer">
		<ul id="w3c">
			<li>
				<a id="vxhtml" href="http://validator.w3.org/check/referer">XHTML 1.1 valide</a>
			</li>
			<li>
				<a id="vcss" href="http://jigsaw.w3.org/css-validator/check/referer">CSS 2.0 valide</a>
			</li>
		</ul>
		<p>
			Designed for <a href="http://blog.famillecoollet.com">Remi</a> by <a href="http://blog.ulysses.fr">Trashy</a>
		</p>
	</div>
</body>
</html>
<?php
}
