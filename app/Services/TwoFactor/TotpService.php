<?php

namespace App\Services\TwoFactor;

use RuntimeException;

/**
 * Time-based One-Time Password (TOTP) implementation service.
 * Generates and validates TOTP codes according to RFC 6238.
 */
class TotpService
{
    public function __construct(
        public int $digits = 6,
        public int $period = 30,
        public string $algorithm = 'sha1',
    ) {}

    /**
     * Generates a random secret key for TOTP authentication.
     *
     * @param  int  $bytes  Number of random bytes to generate
     * @return string Base32 encoded secret key
     */
    public function generateSecret(int $bytes = 20): string
    {
        return $this->base32Encode(random_bytes($bytes));
    }

    /**
     * Generates an otpauth:// URI for QR code generation.
     *
     * @param  string  $label  Account label (e.g. email)
     * @param  string  $secret  Secret key in base32 format
     * @param  string|null  $issuer  Optional issuer name
     * @return string URI for QR code
     */
    public function getOtpAuthUri(string $label, string $secret, ?string $issuer = null): string
    {
        $params = ['secret' => $secret, 'period' => $this->period, 'digits' => $this->digits, 'algorithm' => strtoupper($this->algorithm)];
        if ($issuer !== null && $issuer !== '') {
            $params['issuer'] = $issuer;
        }

        $query = http_build_query($params, arg_separator: '&');
        $encodedLabel = rawurlencode($label);

        return "otpauth://totp/{$encodedLabel}?{$query}";
    }

    /**
     * Verifies a TOTP code against a secret.
     *
     * @param  string  $secret  Base32 encoded secret
     * @param  string  $code  Code to verify
     * @param  int|null  $timestamp  Timestamp to verify against (defaults to current time)
     * @param  int  $window  Number of periods to check before/after timestamp
     * @return bool True if code is valid
     */
    public function verify(string $secret, string $code, ?int $timestamp = null, int $window = 1): bool
    {
        $timestamp ??= time();
        $code = preg_replace('/\D+/', '', $code ?? '');
        if ($code === null || strlen($code) !== $this->digits) {
            return false;
        }

        $secretBytes = $this->base32Decode($secret);
        $timeSlice = (int) floor($timestamp / $this->period);

        for ($i = -$window; $i <= $window; $i++) {
            $calc = $this->totpAt($secretBytes, $timeSlice + $i);
            if (hash_equals($calc, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generates the current TOTP code for a secret.
     *
     * @param  string  $secret  Base32 encoded secret
     * @param  int|null  $timestamp  Timestamp to generate code for (defaults to current time)
     * @return string Generated TOTP code
     */
    public function currentCode(string $secret, ?int $timestamp = null): string
    {
        $timestamp ??= time();
        $secretBytes = $this->base32Decode($secret);
        $timeSlice = (int) floor($timestamp / $this->period);

        return $this->totpAt($secretBytes, $timeSlice);
    }

    /**
     * Generates a TOTP code for a specific time slice.
     *
     * @param  string  $secretBytes  Raw secret key bytes
     * @param  int  $timeSlice  Time period number
     * @return string Generated TOTP code
     */
    protected function totpAt(string $secretBytes, int $timeSlice): string
    {
        $binaryTime = pack('N*', 0).pack('N*', $timeSlice);
        $hash = hash_hmac($this->algorithm, $binaryTime, $secretBytes, binary: true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncatedHash = substr($hash, $offset, 4);
        $value = unpack('N', $truncatedHash)[1] & 0x7FFFFFFF;
        $mod = 10 ** $this->digits;
        $code = (string) ($value % $mod);

        return str_pad($code, $this->digits, '0', STR_PAD_LEFT);
    }

    /**
     * Encodes binary data as base32 string.
     *
     * @param  string  $data  Raw binary data
     * @return string Base32 encoded string
     */
    protected function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binaryString = '';
        foreach (str_split($data) as $char) {
            $binaryString .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        $chunks = str_split($binaryString, 5);
        $output = '';
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $output .= $alphabet[bindec($chunk)];
        }
        // pad to nearest 8 chars using '=' per RFC
        while (strlen($output) % 8 !== 0) {
            $output .= '=';
        }

        return $output;
    }

    /**
     * Decodes base32 string to binary data.
     *
     * @param  string  $data  Base32 encoded string
     * @return string Raw binary data
     *
     * @throws RuntimeException If input contains invalid base32 characters
     */
    protected function base32Decode(string $data): string
    {
        $data = strtoupper($data);
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $data = rtrim($data, '=');
        $binaryString = '';
        foreach (str_split($data) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) {
                throw new RuntimeException('Invalid base32 character.');
            }
            $binaryString .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $bytes = '';
        $chunks = str_split($binaryString, 8);
        foreach ($chunks as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }

        return $bytes;
    }
}
