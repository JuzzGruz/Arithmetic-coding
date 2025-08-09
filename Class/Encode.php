<?php

class AdaptiveArithmeticCoder {
    const MAX_RANGE = 65536;
    const HALF = 32768;
    const QUARTER = 16384;

    private $low = 0;
    private $high = self::MAX_RANGE - 1;
    private $pendingBits = 0;
    private $output = '';
    private $frequencies = [];
    private $total = 0;

    public function encode(string $input): string {
        $this->initializeFrequencies(str_split($input));

        foreach (str_split($input) as $char) {
            $cumulative = $this->buildCumulative();
            $range = $this->high - $this->low + 1;

            $lowBound = $cumulative[$char]['low'];
            $highBound = $cumulative[$char]['high'];

            $this->high = $this->low + intdiv($range * $highBound, $this->total) - 1;
            $this->low = $this->low + intdiv($range * $lowBound, $this->total);

            $this->normalize();

            // обновляем частоты
            $this->frequencies[$char]++;
            $this->total++;
        }

        $this->pendingBits++;
        $this->writeBit(($this->low < self::QUARTER) ? 0 : 1);
        return $this->output;
    }

    private function initializeFrequencies(array $alphabet): void {
        foreach ($alphabet as $char) {
            $this->frequencies[$char] = 1; // начальная частота
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

    private function writeBit(int $bit): void {
        $this->output .= (string)$bit;
        while ($this->pendingBits > 0) {
            $this->output .= (string)(1 - $bit);
            $this->pendingBits--;
        }
    }

    private function normalize(): void {
        while (true) {
            if ($this->high < self::HALF) {
                $this->writeBit(0);
                $this->low <<= 1;
                $this->high = ($this->high << 1) | 1;
            } elseif ($this->low >= self::HALF) {
                $this->writeBit(1);
                $this->low = ($this->low - self::HALF) << 1;
                $this->high = (($this->high - self::HALF) << 1) | 1;
            } elseif ($this->low >= self::QUARTER && $this->high < 3 * self::QUARTER) {
                $this->pendingBits++;
                $this->low = ($this->low - self::QUARTER) << 1;
                $this->high = (($this->high - self::QUARTER) << 1) | 1;
            } else {
                break;
            }
        }
    }
}
