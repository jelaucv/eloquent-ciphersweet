<?php


namespace ParagonIE\EloquentCipherSweet\Observers;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EncryptingObserver
{
    /**
     * @param Model|CipherSweet $model
     * @throws \ParagonIE\CipherSweet\Exception\CryptoOperationException
     * @throws \SodiumException
     */
    public function retrieved(Model $model)
    {
        $model->setRawAttributes($model->cipherSweet()->decryptRow($this->getAttributes()), true);
    }

    /**
     * @param Model|CipherSweet $model
     * @throws \ParagonIE\CipherSweet\Exception\ArrayKeyException
     * @throws \ParagonIE\CipherSweet\Exception\CryptoOperationException
     * @throws \SodiumException
     */
    public function saving(Model $model)
    {
        $model->setRawAttributes($model->cipherSweet()->encryptRow($this->getAttributes()));
    }
}
