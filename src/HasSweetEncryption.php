<?php


namespace ParagonIE\EloquentCipherSweet;


use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\EloquentCipherSweet\Observers\EncryptingObserver;
use ParagonIE\EloquentCipherSweet\Observers\IndexingObserver;

trait HasSweetEncryption
{
    /**
     * @return void
     */
    protected static function bootHasSweetEncryption()
    {
        static::observe(EncryptingObserver::class);

        $model = new static;

        static::configureCipherSweetIndexes($model->cipherSweet(), $model->cipherSweetFields ?? []);
    }

    /**
     * Configures which fields are encrypted and as what type. Additionally configures a source of additional
     * authenticated data.
     *
     * @param EncryptedRow $encryptedRow
     * @return void
     */
    protected static function configureCipherSweetFields(EncryptedRow $encryptedRow, array $cipherSweetFields)
    {
        foreach ($cipherSweetFields as $field => $type) {
            $aadSource = '';

            if (is_array($type)) {
                list($type, $aadSource) = $type;
            }

            $encryptedRow->addField($field, $type, $aadSource);
        }
    }
}