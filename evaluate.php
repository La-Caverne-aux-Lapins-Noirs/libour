#!/usr/bin/php
<?php

$soviets = "https://github.com/Damdoshi/libour-soviets.git";
$allies = "https://github.com/Damdoshi/libour-allies.git";

$now = date("H_i", time());
mkdir($dir = "nbr$now/", 0755, true);
chdir($dir);

function get_working_functions($link, $name, $fine = true)
{
    if ($link != NULL)
    {
	system("git clone $link");
	chdir($name);
	system("make");
    }
    else
	chdir($name);

    chdir("cracks");
    $ret = explode("\n", shell_exec("make"));
    $ret = $ret[count($ret) - 1];
    $sources = explode("\n", shell_exec("ls src/*.c 2> /dev/null || echo -n"));
    $traces = explode("\n", shell_exec("ls src/*.trace 2> /dev/null || echo -n"));

    foreach ($traces as &$tra)
	$tra = str_replace(".trace", "", str_replace("src/", "", $tra));
    foreach ($sources as &$sou)
	$sou = str_replace(".c", "", str_replace("src/", "", $sou));

    $fine = [];
    $bad = [];
    foreach ($sources as &$src)
    {
	if ($src == "")
	    continue ;
	if (!array_search($src, $traces))
	    $fine[] = $src;
	else
	    $bad[] = $src;
    }
    chdir("../..");
    if ($fine)
	return ($fine);
    return ($bad);
}

$sovfine = get_working_functions($soviets, "libour-soviets", true);
$allfine = get_working_functions($allies, "libour-allies", true);

mkdir("soviets-under-attack");
system("cp libour-soviets/libour* soviets-under-attack/");
system("cp -r libour-allies/cracks soviets-under-attack/");
$sovbad = get_working_functions(NULL, "soviets-under-attack", false);

mkdir("allies-under-attack");
system("cp libour-allies/libour* allies-under-attack/");
system("cp -r libour-allies/cracks allies-under-attack/");
$allbad = get_working_functions(NULL, "allies-under-attack", false);

$soviet_score = 0;
foreach ($sovfine as $func)
{
    if (array_search($func, $allbad))
	$soviet_score += 1;
}

$allies_score = 0;
foreach ($allfine as $func)
{
    if (array_search($func, $sovbad))
	$allies_score += 1;
}

echo "Soviet score: $soviet_score\n";
echo "Allies score: $allies_score\n";
