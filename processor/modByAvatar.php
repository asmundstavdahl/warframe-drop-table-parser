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

function process_modByAvatar($table, &$byItem, &$bySource)
{
    $sourceType = "Enemy drop";

    foreach ($table->childNodes as $tr) {
        if ($tr->getAttribute("class") == "blank-row") {
            $nextTrIsMainSource = true;
            continue;
        }

        if ($tr->childNodes[0]->nodeName == "th") {
            $enemyName = trim($tr->childNodes[0]->textContent);
            $enemyDropRate = trim($tr->childNodes[1]->textContent);

            $source = [$enemyName, $enemyDropRate];
        } else {
            # children are td
            $item = trim($tr->childNodes[1]->textContent);

            $item = makeItemMoreSearchable($item);

            $dropRate = preg_replace("_[^.0-9]_", "", $tr->childNodes[2]->textContent);
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

            if (!@$source[1]) {
                echo "No \$source[1]. Source was:\n".print_r($source, true)."\nAnd the tr was:\n".print_r($tr, true)."\n\n";
            }

            $bySource[$source[0]][] = [
                "specifically" => $source[1],
                "item" => $item,
                "dropRate" => $dropChancePercent,
                "sourceType" => $sourceType,
            ];
        }
    }
}
