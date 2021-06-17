<?php


namespace ParagonIE\EloquentCipherSweet\Observers;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ParagonIE\EloquentCipherSweet\HasSweetIndexes;

class
IndexingObserver
{
    /**
     * @param Model|\ParagonIE\EloquentCipherSweet\HasSweetIndexes $model
     */
    public function created(Model $model)
    {
        DB::table('blind_indexes')->insertOrIgnore(
            array_map(
                function ($row) use ($model) { $row['foreign_id'] = $model->getKey(); return $row; },
                $model->cipherSweet()->getAllBlindIndexes($model->toArray()))
            );
    }

    /**
     * @param Model $model
     */
    public function updating(Model $model)
    {
        $dirty = array_reduce($model->cipherSweet()->listEncryptedFields(), function ($carry, $key) use ($model) {
            if ($model->isDirty($key)) {
                $carry[$key] = $model[$key];
            }

            return $carry;
        }, []);

        $blinds = $model->cipherSweet()->getAllBlindIndexes($dirty);
        if (!$blinds) {
            return;
        }

        $types = array_pluck($blinds, 'type');
        DB::table('blind_indexes')
            ->whereIn('type', $types)
            ->where('foreign_id', $model->getKey())
            ->delete();

        DB::table('blind_indexes')->insertOrIgnore(
            array_map(
                function ($row) use ($model) { $row['foreign_id'] = $model->getKey(); return $row; },
                $blinds
            ));
    }

    /**
     * @param Model|HasSweetIndexes $model
     * @throws \SodiumException
     */
    public function deleting(Model $model)
    {
        $blinds = $model->cipherSweet()->getAllBlindIndexes($dirty);
        if (!$blinds) {
            return;
        }

        $types = array_pluck($blinds, 'type');
        DB::table('blind_indexes')
            ->whereIn('type', $types)
            ->where('foreign_id', $model->getKey())
            ->delete();
    }
}