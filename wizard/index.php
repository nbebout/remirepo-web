<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="content-type" />
		<title>Remi's RPM repository</title>
		<link href="../enterprise/7/remi/x86_64/repoview/layout/repostyle.css" type="text/css" rel="stylesheet" />
		<meta content="index,follow" name="robots" />
		<link rel="shortcut icon" href="/favicon.ico" />
</head>
<?php
define('FC_EOL', 21);
define('EL_EOL', 4);
define('COUNTER', __DIR__ . "/counter.txt");

$osvers = [
    'RHEL 7'    => '5.4',
    'RHEL 6'    => '5.3',
    'RHEL 5'    => '5.1',
    'CentOS 7'  => '5.4',
    'CentOS 6'  => '5.3',
    'CentOS 5'  => '5.1',
    'Fedora 24' => '5.6',
    'Fedora 23' => '5.6',
    'Fedora 22' => '5.6',
    'Fedora 21' => '5.6',
];
$types = [
    'base' => 'Single version',
    'scl'  => 'Multiple versions', 
];
$phpvers = [
    '7.0' => 'remi-php70',
    '5.6' => 'remi-php56',
    '5.5' => 'remi-php55',
    '5.4' => 'remi',
];
$phpname = [
    '7.0' => '7.0.4 (active support until Dec 2017)',
    '5.6' => '5.6.19 (active support until Dec 2016)',
    '5.5' => '5.5.33 (security support until Jul 2016)',
    '5.4' => '5.4.45 (no upstream support since Sept 2015)',
];
$php  = (isset($_POST['php'])  && isset($phpvers[$_POST['php']]) ? $_POST['php'] : false);
$os   = (isset($_POST['os'])   && isset($osvers[$_POST['os']])   ? $_POST['os'] : false);
$type = (isset($_POST['type']) && isset($types[$_POST['type']])  ? $_POST['type'] : false);

?>
<body>
	<div id="page">
		<div id="top">
			<h1><span><a href="/">Remi's RPM repository - Configuration wizard</a></span></h1>
		</div>
		<p id="prelude">
			<a href="http://blog.remirepo.net/">Blog</a> | 
			<a href="http://forum.remirepo.net/">Forums</a> | 
			<a href="http://rpms.remirepo.net/">Repository</a>
		</p>
		<div id="wrapper">
			<div id="main">
				<div id="content">
                                    <h2>Operating system and version selection</h2>
                                    <form method='post'>
                                    <ul class="pkglist">
                                    <li><p>Operating system:
                                        <select name='os' onChange='submit()'>
                                        <option value=''>--</option>
<?php
                                        $prev = false;
                                        foreach($osvers as $osver => $phpver) {
                                            list($dist, $ver) = explode(' ', $osver, 2);
                                            if ($dist != $prev) {
                                                if ($prev) echo "</optgroup>";
                                                printf("<optgroup label='%s'>", $prev=$dist);
                                            }
                                            printf("<option value='%s' %s>&nbsp;&nbsp;&nbsp;&nbsp;%s</option>", $osver, ($osver===$os ? 'selected' : ''), $osver);
                                        }
?>
                                        </optgroup></select>
                                    </p></li>
                                    <li><p>PHP version:
                                        <select name='php' onChange='submit()'>
                                        <option value=''>--</option>
<?php
                                        foreach($phpvers as $phpver => $repo) printf("<option value='%s' %s>%s</option>", $phpver, ($phpver===$php ? 'selected' : ''), $phpname[$phpver]);
?>
                                        </select>
                                    </p></li>
                                    <li><p>Type of installation:
                                        <select name='type' onChange='submit()'>
                                        <option value=''>--</option>
<?php
                                        foreach($types as $typeref => $name) printf("<option value='%s' %s>%s</option>", $typeref, ($typeref===$type ? 'selected' : ''), $name);
?>
                                        </select>
                                    </p></li>
                                    </ul>
                                    </form>
                                    <h2>Wizard answer</h2>
                                    <ul class="pkglist">
<?php
$counter = intval(@file_get_contents(COUNTER));

//printf("<p>Debug: $os, $type, $php (%s)</p>", print_r($_POST, true));
$err = false;
if ($os) {
    list($dist, $ver) = explode(' ', $os, 2);    
    if (($dist == 'Fedora' && $ver<=FC_EOL) || ($dist != 'Fedora' && $ver<=EL_EOL)) {
        printf("<li><b>%s</b> have reached its <b>end of life</b>, upgrade is strongly recommended.</li><br />", $os);
    }
}
if ($php && $os) {
    printf("<li><b>%s</b> provides PHP version <b>%s</b> in its official repository</li><br />", $os, $osvers[$os]);

    if ($ver < 6 && version_compare($php, '7.0', '>=')) {
        printf("<li>Sorry, but PHP version <b>%s</b> is not available for <b>%s</b>, you need to run a more recent OS.</li><br />", $php, $os);
        $err = true;
    }
}
if ($php && $os && $type && !$err) {
    if ($dist == 'Fedora') {
        $yum = 'dnf';
        printf("<li>Command to install the Remi repository configuration package:");
        printf("<pre>    $yum install http://rpms.remirepo.net/fedora/remi-release-%d.rpm</pre>", $ver);
        printf("</li><br />");
    
    } else {
        $yum = 'yum';
        printf("<li>Command to install the EPEL repository configuration package:");
        if ($ver < 6) {
            printf("<pre>    wget https://dl.fedoraproject.org/pub/epel/epel-release-latest-%d.noarch.rpm\n", $ver);
            printf("    $yum install epel-release-latest-%d.noarch.rpm\n", $ver);
            printf("</li><br /><li>Command to install the Remi repository configuration package:");
            printf("<pre>    wget http://rpms.remirepo.net/enterprise/remi-release-%d.rpm\n", $ver);
            printf("    $yum install http://rpms.remirepo.net/enterprise/remi-release-%d.rpm</pre>", $ver);
        } else {
            printf("<pre>    $yum install https://dl.fedoraproject.org/pub/epel/epel-release-latest-%d.noarch.rpm</pre>", $ver);
            printf("</li><br /><li>Command to install the Remi repository configuration package:");
            printf("<pre>    $yum install http://rpms.remirepo.net/enterprise/remi-release-%d.rpm</pre>", $ver);
        }
        printf("</li><br />");
        printf("<li>Command to install the yum-utils package (for the yum-config-manager command):");
        printf("<pre>    $yum install yum-utils</pre>");
        printf("</li><br />");
        if ($dist == 'RHEL') {
            printf("<li>On <b>RHEL</b> you (probably) need to enable the <b>optional channel</b> for some dependencies.</li><br />");
            printf("</li><li>Command to enable:");
            if ($ver == 7) {
                printf("<pre>    subscription-manager repos --enable=rhel-7-server-optional-rpms</pre>");
            } else {
                printf("<pre>    rhn-channel --add --channel=rhel-$(uname -i)-server-optional-6</pre>");
            }
            printf("</li><br />");
        }
    }
    if ($type == 'base') {
        printf("<li>You want a <b>single version </b> which means replacing base packages from the distribution</li><br />");

        if (version_compare($php, $osvers[$os], '<')) {
            printf("<li>Sorry, but PHP version older than <b>%s</b> are not available for <b>%s</b>, try multiple versions.</li><br />", $osvers[$os], $os);

        } else if (version_compare($php, $osvers[$os], '=')) {
            printf("<li>PHP version <b>%s</b> packages are available for <b>%s</b> in <b>remi</b> repository</li><br />", $php, $os);
            printf("<li>Command to upgrade:");
            printf("<pre>    $yum --enablerepo=remi update 'php*'</pre>");
            printf("</li><br />");
            printf("<li>Command to install additional packages:");
            printf("<pre>    $yum --enablerepo=remi install php-xxx</pre>");
            printf("</li><br />");

        } else {
            printf("<li>PHP version <b>%s</b> packages are available for <b>%s</b> in <b>%s</b> repository</li><br />", $php, $os, $phpvers[$php]);
            if ($ver < 6) {
                printf("<li>You have to enable the repository by setting <b>enabled=1</b> in the [%s] section of /etc/yum.repos.d/remi.repo", $phpvers[$php]);
            } else {
                printf("<li>Command to enable the repository:");
                if ($dist == 'Fedora') {
                        printf("<pre>    dnf config-manager --set-enabled %s</pre>", $phpvers[$php]);
                } else {
                        printf("<pre>    yum-config-manager --enable %s</pre>", $phpvers[$php]);
                }
            }
            printf("</li><br />");
            printf("<li>Command to upgrade (the repository only provides PHP):");
            printf("<pre>    $yum update</pre>");
            printf("</li><br />");
            printf("<li>Command to install additional packages:");
            printf("<pre>    $yum install php-xxx</pre>");
            printf("</li><br />");
            printf("<li>Command to check the installed version and available extensions:");
            printf("<pre>    php --version\n    php --modules</pre>");
            printf("</li><br />");
        }
        $counter++;
        @file_put_contents(COUNTER, "$counter\n");
    } else {
        printf("<li>You want <b>multiple versions </b> which means using a <a href='https://www.softwarecollections.org/en/'>Software Collection</a></li><br />");
        $scl='php'.str_replace('.', '', $php);
        
        if ($dist=='Fedora' || version_compare($php, '5.6', '<')) {
            printf("<li>The <b>%s</b> collection is available in the <b>remi</b> repository</li><br />", $scl);
            printf("<li>Command to install:");
            printf("<pre>    $yum --enablerepo=remi install %s</pre>", $scl);
            printf("</li><br />");
            printf("<li>Command to install additional packages:");
            printf("<pre>    $yum --enablerepo=remi install %s-php-xxx</pre>", $scl);
            printf("</li><br />");
        } else {
            printf("<li>The <b>%s</b> collection is available in the <b>remi-safe</b> repository</li><br />", $scl);
            printf("<li>Command to install:");
            printf("<pre>    $yum install %s</pre>", $scl);
            printf("</li><br />");
            printf("<li>Command to install additional packages:");
            printf("<pre>    $yum install %s-php-xxx</pre>", $scl);
            printf("</li><br />");
        }
        printf("<li>Command to check the installed version and available extensions:");
        printf("<pre>    %s --version\n    %s --modules</pre>", $scl, $scl);
        printf("</li><br />");
        $counter++;
        @file_put_contents(COUNTER, "$counter\n");
    }
} else if (!$os) {
    echo "<li><p>Please select the operating system you are running.</p></li>";

} else if (!$php) {
    echo "<li><p>Please select PHP version you want to use.</p></li>";

} else if (!$err) {
    echo "<li><p>Please select installation type</p></li>";
}
?>
                                    </ul>
                                </div>
			</div>
			<div id="sidebar">
				<h2>More information</h2>
				<ul class="levbarlist">
					<li><a href="http://blog.remirepo.net/pages/Config-en" class="nlink" title="Repository configuration">Repository configuration</a></li>
					<li><a href="http://blog.remirepo.net/pages/English-FAQ"  class="nlink" title="F.A.Q.">F.A.Q.</a></li>
					<li><?php
					printf("<b>%d answers</b> given", $counter);
					?></li>
				</ul><br /><br /><br />
<form action="https://www.paypal.com/cgi-bin/webscr" method="post"><div>
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
" />
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" name="submit" alt="PayPal - The safer, easier way to pay online!" />
<img alt="" src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" width="1" height="1" />
</div></form>

			</div>
		</div>
	        <hr style="clear:both;"/>
	        
	</div>
</body>
</html>
