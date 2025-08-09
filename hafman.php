<?php

class Node {
    public $char;
    public $freq;
    public $left;
    public $right;

    public function __construct($char, $freq, $left = null, $right = null) {
        $this->char = $char;
        $this->freq = $freq;
        $this->left = $left;
        $this->right = $right;
    }
}

function buildFrequencyTable($string) {
    $freq = [];
    for ($i = 0; $i < strlen($string); $i++) {
        $char = $string[$i];
        if (!isset($freq[$char])) {
            $freq[$char] = 0;
        }
        $freq[$char]++;
    }
    return $freq;
}

function buildHuffmanTree($freqTable) {
    $heap = [];

    foreach ($freqTable as $char => $freq) {
        $heap[] = new Node($char, $freq);
    }

    usort($heap, fn($a, $b) => $a->freq - $b->freq);

    while (count($heap) > 1) {
        $left = array_shift($heap);
        $right = array_shift($heap);
        $merged = new Node(null, $left->freq + $right->freq, $left, $right);
        $heap[] = $merged;

        usort($heap, fn($a, $b) => $a->freq - $b->freq);
    }

    return $heap[0];
}

function buildCodes($node, $prefix = "", &$codeTable = []) {
    if ($node->char !== null) {
        $codeTable[$node->char] = $prefix;
        return;
    }

    buildCodes($node->left, $prefix . "0", $codeTable);
    buildCodes($node->right, $prefix . "1", $codeTable);
}

function encode($string, $codeTable) {
    $encoded = '';
    for ($i = 0; $i < strlen($string); $i++) {
        $encoded .= $codeTable[$string[$i]];
    }
    return $encoded;
}

function decode($encoded, $tree) {
    $decoded = '';
    $node = $tree;

    for ($i = 0; $i < strlen($encoded); $i++) {
        $node = $encoded[$i] === '0' ? $node->left : $node->right;
        if ($node->char !== null) {
            $decoded .= $node->char;
            $node = $tree;
        }
    }

    return $decoded;
}

// Example usage:
$input = "hello huffman";

$freqTable = buildFrequencyTable($input);
$tree = buildHuffmanTree($freqTable);
$codeTable = [];
buildCodes($tree, "", $codeTable);

$encoded = encode($input, $codeTable);
$decoded = decode($encoded, $tree);

echo "Original: $input\n";
echo "Encoded: $encoded\n";
echo "Decoded: $decoded\n";
