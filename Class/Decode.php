<?php

class AdaptiveArithmeticDecoder {
    const MAX_RANGE = 65536;
    const HALF = 32768;
    const QUARTER = 16384;

    private $low = 0;
    private $high = self::MAX_RANGE - 1;
    private $code = 0;
    private $bitIndex = 0;
    private $inputBits;
    private $frequencies = [];
    private $total = 0;

    public function decode(string $encodedBits, string $alphabet, int $symbolCount): string {
        $this->inputBits = str_split($encodedBits);
        $this->initializeFrequencies(str_split($alphabet));
        $this->code = $this->readBits(16);

        $output = '';
        for ($i = 0; $i < $symbolCount; $i++) {
            $cumulative = $this->buildCumulative();
            $range = $this->high - $this->low + 1;

            $value = intdiv((($this->code - $this->low + 1) * $this->total - 1), $range);

            foreach ($cumulative as $char => $bounds) {
                if ($value >= $bounds['low'] && $value < $bounds['high']) {
                    $output .= $char;

                    // обновляем границы
                    $this->high = $this->low + intdiv($range * $bounds['high'], $this->total) - 1;
                    $this->low = $this->low + intdiv($range * $bounds['low'], $this->total);

                    $this->normalize();

                    // обновляем частоты
                    $this->frequencies[$char]++;
                    $this->total++;

                    break;
                }
            }
        }

        return $output;
    }

    private function initializeFrequencies(array $alphabet): void {
        foreach ($alphabet as $char) {
            $this->frequencies[$char] = 1;
        }
        $this->total = count($this->frequencies);
    }

    private function buildCumulative(): array {
        $result = [];
        $sum = 0;
        foreach ($this->frequencies as $char => $freq) {
            $result[$char] = ['low' => $sum, 'high' => $sum + $freq];
            $sum += $freq;
        }
        return $result;
    }

    private function readBit(): int {
        return ($this->bitIndex < count($this->inputBits)) ? (int)$this->inputBits[$this->bitIndex++] : 0;
    }

    private function readBits(int $count): int {
        $value = 0;
        for ($i = 0; $i < $count; $i++) {
            $value = ($value << 1) | $this->readBit();
        }
        return $value;
    }

    private function normalize(): void {
        while (true) {
            if ($this->high < self::HALF) {
                // ничего не делаем
            } elseif ($this->low >= self::HALF) {
                $this->low -= self::HALF;
                $this->high -= self::HALF;
                $this->code -= self::HALF;
            } elseif ($this->low >= self::QUARTER && $this->high < 3 * self::QUARTER) {
                $this->low -= self::QUARTER;
                $this->high -= self::QUARTER;
                $this->code -= self::QUARTER;
            } else {
                break;
            }

            $this->low = ($this->low << 1) & (self::MAX_RANGE - 1);
            $this->high = (($this->high << 1) & (self::MAX_RANGE - 1)) | 1;
            $this->code = (($this->code << 1) & (self::MAX_RANGE - 1)) | $this->readBit();
        }
    }
}
