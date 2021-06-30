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
use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\EloquentCipherSweet\Observers\IndexingObserver;

/**
 * Trait HasSweetIndexes
 * @package ParagonIE\EloquentCipherSweet
 *
 * @method EloquentBuilder whereBlind(string $indexName, string $value)
 */
trait HasSweetIndexes
{
    use CipherSweet;

    private static $indexToField = [];

    /**
     * @param EloquentBuilder $query
     * @param string $indexName
     * @param string|array<string,mixed> $value
     * @return EloquentBuilder
     */
    public function scopeWhereBlind(EloquentBuilder $query, string $indexName, $value)
    {
        return $query->whereExists(function (Builder $query) use ($indexName, $value): Builder {
            /** @var CipherSweetEngine $engine */
            $engine = $this->cipherSweet();
            $table = $this->getTable();

            $column = static::$indexToField[$indexName];
            $columns = is_string($column) ? [$column => $value] : $value;

            $index = $this->cipherSweet()->getBlindIndex($indexName, $columns);

            return $query->selectRaw('1')
                ->from('blind_indexes')
                ->whereRaw(
                    "blind_indexes.foreign_id = $table.{$this->getKeyName()}",
                )
                ->where(
                    'blind_indexes.type',
                    $index['type']
                )
                ->where(
                    'blind_indexes.value',
                    $index['value']
                );
        });
    }

    /**
     * @return void
     */
    protected static function bootHasSweetIndexes()
    {
        static::observe(IndexingObserver::class);
    }

    /**
     * Configures blind indexes.
     *
     * @param EncryptedRow $encryptedRow
     * @return void
     */
    protected static function configureCipherSweetIndexes(EncryptedRow $encryptedRow, array $cipherSweetIndexes)
    {
        foreach ($cipherSweetIndexes as $index => $configuration) {
            $configuration = Arr::wrap($configuration);

            $column = $configuration[0];
            $transformations = array_map(fn($class) => app($class), (array)$configuration[1] ?? []);
            $isSlow = $configuration[2] ?? false;
            $filterBits = $configuration[3] ?? 256;
            $hashConfig = $configuration[4] ?? [];

            if (is_array($column)) {
                array_map(fn($column) => $encryptedRow->addTextField($column), $column);
                $compoundIndex = new CompoundIndex($index, $column, (int) $filterBits, !$isSlow, $hashConfig);

                foreach ($transformations as $transformation) {
                    $compoundIndex->addRowTransform($transformation);
                }

                $encryptedRow->addCompoundIndex($compoundIndex);
            } else {
                $encryptedRow->addTextField($column);
                $encryptedRow->addBlindIndex($column, new BlindIndex($index, $transformations, (int) $filterBits, !$isSlow, $hashConfig));
            }

            static::$indexToField[$index] = $column;
        }
    }
}
