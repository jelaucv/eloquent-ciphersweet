<?php

namespace ParagonIE\EloquentCipherSweet;


use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\CipherSweet as CipherSweetEngine;
use ParagonIE\CipherSweet\CompoundIndex;
use ParagonIE\CipherSweet\Constants;
use ParagonIE\CipherSweet\Contract\TransformationInterface;

/**
 * Trait CipherSweet
 *
 * Makes integrating CipherSweet with Eloquent ORM much easier.
 *
 * @package ParagonIE\EloquentCipherSweet
 */
trait CipherSweet
{
    /**
     * @param  object|array|string  $classes
     * @return void
     * @throws \RuntimeException
     */
    abstract public static function observe($classes);


    /**
     * @return EncryptedRow
     */
    public function cipherSweet()
    {
        static $cipherSweetEncryptedRow;
        if (!$cipherSweetEncryptedRow) {
            $cipherSweetEncryptedRow = new EncryptedRow(
                app(CipherSweetEngine::class),
                (new static)->getTable()
            );
        }
        
        $model = new static;
        if (isset($model->cipherSweetIndexes)) {
            static::configureCipherSweetIndexes($model->cipherSweet(), $model->cipherSweetIndexes);
        }

        return $cipherSweetEncryptedRow;
    }

    /**
     * @return string
     */
    abstract public function getTable();

    /**
     * @return string
     */
    abstract public function getKeyName();

    /**
     * @param array $attributes
     * @param bool $sync
     * @return $this
     */
    abstract public function setRawAttributes(array $attributes, $sync = false);

    /**
     * @return array
     */
    abstract public function getAttributes();
}
