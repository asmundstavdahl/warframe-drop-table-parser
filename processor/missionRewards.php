<?php

# getElementsByTagName previousSibling nodeValue attributes getNamedItem value

/*
readonly string $nodeName ;
string $nodeValue ;
readonly int $nodeType ;
readonly DOMNode $parentNode ;
readonly DOMNodeList $childNodes ;
readonly DOMNode $firstChild ;
readonly DOMNode $lastChild ;
readonly DOMNode $previousSibling ;
readonly DOMNode $nextSibling ;
readonly DOMNamedNodeMap $attributes ;
readonly DOMDocument $ownerDocument ;
readonly string $namespaceURI ;
string $prefix ;
readonly string $localName ;
readonly string $baseURI ;
string $textContent ;
DOMNode appendChild ( DOMNode $newnode )
string C14N ([ bool $exclusive [, bool $with_comments [, array $xpath [, array $ns_prefixes ]]]] )
int C14NFile ( string $uri [, bool $exclusive = FALSE [, bool $with_comments = FALSE [, array $xpath [, array $ns_prefixes ]]]] )
DOMNode cloneNode ([ bool $deep ] )
int getLineNo ( void )
string getNodePath ( void )
bool hasAttributes ( void )
bool hasChildNodes ( void )
DOMNode insertBefore ( DOMNode $newnode [, DOMNode $refnode ] )
bool isDefaultNamespace ( string $namespaceURI )
bool isSameNode ( DOMNode $node )
bool isSupported ( string $feature , string $version )
string lookupNamespaceUri ( string $prefix )
string lookupPrefix ( string $namespaceURI )
void normalize ( void )
DOMNode removeChild ( DOMNode $oldnode )
DOMNode replaceChild ( DOMNode $newnode , DOMNode $oldnode )
*/

function process_missionRewards($table, &$byItem, &$bySource)
{
    $sourceType = "Mission reward";
    $bySource[$sourceType] = [];

    $nextTrIsMainSource = true;

    $source = ["?mission?"];

    foreach ($table->childNodes as $tr) {
        if ($tr->nodeName != "tr") {
            exit("{$tr->nodeName} is not tr");
        }

        if ($tr->getAttribute("class") == "blank-row") {
            continue;
        }

        if ($tr->childNodes[0]->nodeName == "th") {
            if ($nextTrIsMainSource) {
                $newMainSource = trim($tr->childNodes[0]->textContent);
                $source = [$newMainSource];
                $nextTrIsMainSource = false;
            } else {
                $newSourceSpecifier = trim($tr->childNodes[0]->textContent);
                $source[1] = $newSourceSpecifier;
            }
        } else {
            # children are td
            $item = trim($tr->childNodes[0]->textContent);
            $dropRate = preg_replace("_[^.0-9]_", "", $tr->childNodes[1]->textContent);
            $dropChancePercent = floatval($dropRate);
            #echo "{$item} @ " . implode(" : ", $source) . " is {$dropChancePercent}\n";

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
