<?php
declare(strict_types=1);

/* ============================================================
   Lightweight validation helpers.

   Usage:
       $v = new Validator($_POST);
       $v->required('name')->min('name', 2)
         ->required('price')->numeric('price');
       if ($v->fails()) { ...$v->errors()... }
   ============================================================ */

if (!class_exists('Validator')) {
    final class Validator
    {
        private array $data;
        private array $errors = [];

        public function __construct(array $data)
        {
            $this->data = $data;
        }

        private function value(string $field): mixed
        {
            $v = $this->data[$field] ?? null;
            return is_string($v) ? trim($v) : $v;
        }

        public function required(string $field, ?string $label = null): self
        {
            $v = $this->value($field);
            if ($v === null || $v === '' || $v === []) {
                $this->errors[$field][] = ($label ?? ucfirst($field)) . ' is required.';
            }
            return $this;
        }

        public function numeric(string $field, ?string $label = null): self
        {
            $v = $this->value($field);
            if ($v !== null && $v !== '' && !is_numeric($v)) {
                $this->errors[$field][] = ($label ?? ucfirst($field)) . ' must be a number.';
            }
            return $this;
        }

        public function min(string $field, int $len, ?string $label = null): self
        {
            $v = (string) $this->value($field);
            if ($v !== '' && mb_strlen($v) < $len) {
                $this->errors[$field][] = ($label ?? ucfirst($field)) . " must be at least {$len} characters.";
            }
            return $this;
        }

        public function max(string $field, int $len, ?string $label = null): self
        {
            $v = (string) $this->value($field);
            if (mb_strlen($v) > $len) {
                $this->errors[$field][] = ($label ?? ucfirst($field)) . " must not exceed {$len} characters.";
            }
            return $this;
        }

        public function fails(): bool  { return $this->errors !== []; }
        public function passes(): bool { return $this->errors === []; }
        public function errors(): array { return $this->errors; }

        /** Flat list of every error message. */
        public function messages(): array
        {
            return array_merge(...array_values($this->errors ?: [[]]));
        }
    }
}
