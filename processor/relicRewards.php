<?php

function process_relicRewards($table, &$byItem, &$bySource)
{
    $sourceType = "Relic reward";
    $bySource[$sourceType] = [];

    $source = [];

    foreach ($table->childNodes as $tr) {
        if ($tr->getAttribute("class") == "blank-row") {
            continue;
        }

        if ($tr->childNodes[0]->nodeName == "th") {
            # "Axi A1 Relic (Exceptional)"
            $text = $tr->childNodes[0]->textContent;

            $relicName = trim(explode("(", $text)[0]);
            $relicQuality = trim(explode(")", explode("(", $text)[1])[0]);

            $source = [$relicName, $relicQuality];
        } else {
            $item = trim($tr->childNodes[0]->textContent);

            $item = makeItemMoreSearchable($item);

            $dropRate = preg_replace("_[^.0-9]_", "", $tr->childNodes[1]->textContent);
            $dropChancePercent = floatval($dropRate);

            if (!array_key_exists($item, $byItem)) {
                $byItem[$item] = [];
            }

            $byItem[$item][] = [
                "chance" => $dropChancePercent,
                "source" => $source,
                "sourceType" => $sourceType,
            ];

            if (!array_key_exists($source[0], $bySource)) {
                $bySource[$source[0]] = [];
            }

            $bySource[$sourceType][$source[0]][] = [
                "specifically" => $source[1],
                "item" => $item,
                "dropRate" => $dropChancePercent,
            ];
        }
    }
}
