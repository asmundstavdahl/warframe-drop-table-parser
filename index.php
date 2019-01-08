<?php

$cacheTimeFile = "cache_time.txt";
$cacheFile = "cache.html";

if(time() - 3600 > intval(@file_get_contents($cacheTimeFile))){
    #$b = file_get_contents("https://n8k6e2y6.ssl.hwcdn.net/repos/hnfvc0o3jnfvc873njb03enrf56.html");
    # For testing
    $b = file_get_contents("original.html");

    $b = str_replace("</", "\n</", $b);
    $b = str_replace(" & ", " &quot; ", $b);

    file_put_contents($cacheTimeFile, time());
    file_put_contents($cacheFile, $b);
    error_log("Re-cached drop table.");
} else {
    $b = file_get_contents($cacheFile);
}

#echo "HTML is ".strlen($b)." bytes.\n\n\n\n";

$doc = new DOMDocument();
$doc->loadHTML($b);


/**
 * Drops...
 */
$byItem = [];
$bySource = [];

function makeItemMoreSearchable($item)
{
    return $item;#preg_replace('`^([\d,]+)\s+(.*)$`', '\2, \1', $item, 1);
}

/**
 * Each table in the HTML is preceeded by an h3 tag describing its drop source:
 *
 * [id=missionRewards] Missions
 * [id=relicRewards] Relics
 * [id=keyRewards] Keys
 * [id=transientRewards] Dynamic Location Rewards
 * [id=sortieRewards] Sorties
 * [id=cetusRewards] Cetus Bounty Rewards
 * [id=solarisRewards] Orb Vallis Bounty Rewards
 * [id=modByAvatar] Mod Drops by Enemy
 * [id=modByDrop] Mod Drops by Mod
 * [id=blueprintByAvatar] Blueprint/Item Drops by Enemy
 * [id=blueprintByDrop] Blueprint/Item Drops by Blueprint/Item
 * [id=resourceByAvatar] Resource Drops by Enemy
 * [id=resourceByDrop] Resource Drops by Resource
 * [id=sigilByAvatar] Sigil Drops by Enemy
 */
foreach ($doc->getElementsByTagName("table") as $table) {
    $preceedingH3 = $table->previousSibling;
    $tableHeadingText = $preceedingH3->nodeValue;
    $tableHeadingText = str_replace(":", "", $tableHeadingText);
    $tableHeadingId = $preceedingH3->attributes->getNamedItem("id")->value;
    #echo "[{$tableHeadingId}] {$tableHeadingText}";

    $tableProcessorSourceFile = "processor/${tableHeadingId}.php";

    require $tableProcessorSourceFile;

    $tableProcessorFunction = "process_{$tableHeadingId}";
    $tableProcessorFunction($table, $byItem, $bySource);
}

/**
 * For debugging
 */
foreach ($bySource as $sourceType => $sources) {
    break;
    echo "# $sourceType\n";
    foreach ($sources as $source => $drops) {
        echo "## $source\n";
        foreach ($drops as $drop) {
            echo "### {$drop["item"]} ({$drop["dropRate"]}%) ({$drop["specifically"]})\n";
        }
    }
}



$jsonFlags = array_key_exists("pretty", $_GET) ?JSON_PRETTY_PRINT :0;

#$jsonString = json_encode($bySource);#, JSON_PRETTY_PRINT);
echo "Output:";
echo "\n<br>\nby-item:   (bytes) ".file_put_contents("output/by-item.json", json_encode($byItem, $jsonFlags));
echo "\n<br>\nby-source: (bytes) ".file_put_contents("output/by-source.json", json_encode($bySource, $jsonFlags));
echo "\n<br>";
echo "\n<br>\n<pre>".print_r(array_keys($bySource), true)."</pre>";
exit;












function render(string $templateName, array $environmentValues = [])
{
    extract($environmentValues);
    ob_start();
    $templateFile = __DIR__."/templates/{$templateName}.php";
    if (!file_exists($templateFile)) {
        touch($templateFile);
    }
    require $templateFile;
    return ob_get_clean();
}

switch (@$_GET["browse"]) {
    case "search":
        # code...
        break;

    case "by-item":
    default:
        $contentString = render("by-item");
        break;
}

echo render("main", [
    "byItem" => $byItem,
    "bySource" => $bySource,
    "jsonString" => $jsonString,
    "contentHTML" => $contentString,
]);
