<?php declare(strict_types=1);

namespace XBase;

use XBase\Column\ColumnInterface;
use XBase\Enum\TableType;
use XBase\Record\RecordFactory;
use XBase\Record\RecordInterface;
use XBase\Stream\Stream;
use XBase\Table\Saver as TableSaver;
use XBase\Traits\CloneTrait;

class TableEditor extends TableReader
{
    use CloneTrait;

    /**
     * Perform any edits on clone file and replace original file after call `save` method.
     */
    public const EDIT_MODE_CLONE = 'clone';

    /**
     * Perform edits immediately on original file.
     */
    public const EDIT_MODE_REALTIME = 'realtime';

    /**
     * @var bool record property is new
     */
    private $insertion = false;

    protected function resolveOptions(array $options): array
    {
        $options = array_merge(['editMode' => self::EDIT_MODE_CLONE], $options);

        return array_merge(
            parent::resolveOptions($options),
            $options
        );
    }

    protected function open(): void
    {
        switch ($this->table->options['editMode']) {
            case self::EDIT_MODE_CLONE:
                $this->clone();
                $this->table->stream = Stream::createFromFile($this->cloneFilepath, 'rb+');
                break;

            case self::EDIT_MODE_REALTIME:
                $this->table->stream = Stream::createFromFile($this->getFilepath(), 'rb+');
                break;
        }

        //todo find better place for this
        $this->table->handlers['onMemoBlocksDelete'] = function (array $blocks): void {
            $columns = $this->getMemoColumns();

            for ($i = 0; $i < $this->getHeader()->recordCount; $i++) {
                $record = $this->pickRecord($i);
                $save = false;
                foreach ($columns as $column) {
                    if (!$pointer = $record->getGenuine($column->getName())) {
                        continue;
                    }

                    $sub = 0;
                    foreach ($blocks as $deletedPointer => $length) {
                        if ($pointer && $pointer > $deletedPointer) {
                            $sub += $length;
                        }
                    }
                    $save = $sub > 0;
                    $record->setGenuine($column->getName(), $pointer - $sub);
                }
                if ($save) {
                    $this->writeRecord($record);
                }
            }
        };
    }

    public function close(): void
    {
        parent::close();

        if ($this->cloneFilepath && file_exists($this->cloneFilepath)) {
            unlink($this->cloneFilepath);
        }
    }

    public function appendRecord(): RecordInterface
    {
        $this->recordPos = $this->getHeader()->recordCount;
        $this->record = RecordFactory::create($this->table, $this->encoder, $this->recordPos);
        $this->insertion = true;

        return $this->record;
    }

    public function writeRecord(RecordInterface $record = null): self
    {
        $record = $record ?? $this->record;
        if (!$record) {
            return $this;
        }

        $offset = $this->getHeader()->length + ($record->getRecordIndex() * $this->getHeader()->recordByteLength);
        $this->getStream()->seek($offset);
        $this->getStream()
            ->write(RecordFactory::createDataConverter($this->table, $this->encoder)
            ->toBinaryString($record));

        if ($this->insertion) {
            $this->table->header->recordCount++;
        }

        $this->getStream()->flush();

        if (self::EDIT_MODE_REALTIME === $this->table->options['editMode'] && $this->insertion) {
            $this->save();
        }

        $this->insertion = false;

        return $this;
    }

    public function deleteRecord(?RecordInterface $record = null): self
    {
        if ($this->record && $this->insertion) {
            $this->record = null;
            $this->recordPos = -1;

            return $this;
        }

        $record = $record ?? $this->record;
        if (!$record) {
            return $this;
        }

        $record->setDeleted(true);
        $this->writeRecord($record);

        return $this;
    }

    public function undeleteRecord(?RecordInterface $record = null): self
    {
        $record = $record ?? $this->record;
        if (!$record || false === $record->isDeleted()) {
            return $this;
        }

        $record->setDeleted(false);

        $this->getStream()->seek($this->getHeader()->length + ($record->getRecordIndex() * $this->getHeader()->recordByteLength));
        $this->getStream()->write(' ');
        $this->getStream()->flush();

        return $this;
    }

    /**
     * Remove deleted records.
     */
    public function pack(): self
    {
        $newRecordCount = 0;
        for ($i = 0; $i < $this->getRecordCount(); $i++) {
            $r = $this->moveTo($i);

            if ($r->isDeleted()) {
                // remove memo columns
                foreach ($this->getMemoColumns() as $column) {
                    if ($pointer = $this->record->getGenuine($column->getName())) {
                        $this->getMemo()->delete($pointer);
                    }
                }
                continue;
            }

            $r->setRecordIndex($newRecordCount++);
            $this->writeRecord($r);
        }

        $this->getHeader()->recordCount = $newRecordCount;

        $size = $this->getHeader()->length + ($newRecordCount * $this->getHeader()->recordByteLength);
        $this->getStream()->truncate($size);

        if (self::EDIT_MODE_REALTIME === $this->table->options['editMode']) {
            $this->save();
        }

        return $this;
    }

    public function save(): self
    {
        if ($memo = $this->getMemo()) {
            $memo->save();
        }

        $saver = new TableSaver($this->table);
        $saver->save();

        if (self::EDIT_MODE_CLONE === $this->table->options['editMode']) {
            copy($this->cloneFilepath, $this->getFilepath());
        }

        return $this;
    }

    /**
     * @return ColumnInterface[]
     */
    private function getMemoColumns(): array
    {
        $result = [];
        foreach ($this->getColumns() as $column) {
            if (in_array($column->getType(), TableType::getMemoTypes($this->getHeader()->version))) {
                $result[] = $column;
            }
        }

        return $result;
    }
}
