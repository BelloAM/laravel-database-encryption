<?php
/**
 * src/Commands/EncryptModel.php.
 *
 */
namespace Hatcher\DBEncryption\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class EncryptModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encryptable:encryptModel {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt models rows';

    private $attributes = [];
    private $model;

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $class = $this->argument('model');
        $this->model = $this->guardClass($class);
        $this->attributes = $this->model->getEncryptableAttributes();
        $table = $this->model->getTable();
        $pk_id = $this->model->getKeyName();
        $total = $this->model->where('encrypted', 0)->count();
        $this->model::$enableEncryption = false;

        if($total > 0){
            $this->comment($total.' records will be encrypted');
            $bar = $this->output->createProgressBar($total);
            $bar->setFormat('%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

            $records =  $this->model->orderBy($pk_id, 'asc')->where('encrypted', 0)
                ->chunkById(100, function($records) use($table, $bar, $pk_id) {
                    foreach ($records as $record) {
                        $record->timestamps = false;
                        $attributes = $this->getEncryptedAttributes($record);

                        $update_id =  "{$record->{$pk_id}}";
                        DB::table($table)->where($pk_id, $update_id)->update($attributes);
                        $bar->advance();
                        $record = null;
                        $attributes = null;
                    }
            });

            $bar->finish();
        }

        $this->comment('Finished encryption');
    }

    /**
     * Get Encrypted Attributes.
     * @param $record
     * @return int[]
     */
    private function getEncryptedAttributes($record)
    {
        $encryptedFields = ['encrypted' => 1];

        foreach ($this->attributes as $attribute) {
            $raw = $record->getOriginal($attribute);
            $encryptedFields[$attribute] = $this->model->encryptAttribute($raw);
        }
        return $encryptedFields;
    }

    private function validateHasEncryptedColumn($model)
    {
        $table = $model->getTable();
        $database = $model->getDatabaseName();
        $table = preg_replace('/^'.$database.'\./', '', $table);
        if (! Schema::hasColumn($table, 'encrypted')) {
            $this->comment('Creating encrypted column');
            Schema::table($table, function (Blueprint $table) {
                $table->tinyInteger('encrypted')->default(0);
            });
        }
    }

    /**
     * @param $class
     * @return Model
     * @throws Exception
     */
    public function guardClass($class)
    {
        if (!class_exists($class))
            throw new Exception("Class {$class} does not exists");
        $model = new $class();
        $this->validateHasEncryptedColumn($model);
        return $model;
    }
}
