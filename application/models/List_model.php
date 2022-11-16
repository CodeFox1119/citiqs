<?php
    declare(strict_types=1);

    require_once APPPATH . 'interfaces/InterfaceCrud_model.php';
    require_once APPPATH . 'interfaces/InterfaceValidate_model.php';
    require_once APPPATH . 'abstract/AbstractSet_model.php';

    if (!defined('BASEPATH')) exit('No direct script access allowed');

    Class List_model extends AbstractSet_model implements InterfaceCrud_model, InterfaceValidate_model
    {

        public $id;
        public $vendorId;
        public $list;
        public $active;

        private $table = 'tbl_lists';

        protected function setValueType(string $property,  &$value): void
        {
            $this->load->helper('validate_data_helper');
            if (!Validate_data_helper::validateNumber($value)) return;

            if ($property === 'id' || $property === 'vendorId') {
                $value = intval($value);
            }

            return;
        }

        protected function getThisTable(): string
        {
            return $this->table;
        }

        public function insertValidate(array $data): bool
        {
            if (isset($data['vendorId']) && isset($data['list'])) {
                return $this->updateValidate($data);
            }
            return false;
        }

        public function updateValidate(array $data): bool
        {
            $this->load->helper('validate_data_helper');
            if (!count($data)) return false;
            if (isset($data['vendorId']) && !Validate_data_helper::validateInteger($data['vendorId'])) return false;
            if (isset($data['list']) && !Validate_data_helper::validateStringImproved($data['list'])) return false;
            if (isset($data['active']) && !($data['active'] === '1' || $data['active'] === '0')) return false;
            return true;
        }

        /**
         * checkIsExists
         *
         * This method checks is list with set value already exists for vendor with venodrId
         * @see AbstractCrud_model::readImproved
         * @return boolean
         */
        private function checkIsExists(): bool
        {
            $where = [
                $this->table . '.vendorId' => $this->vendorId,
                $this->table . '.list' => $this->list,
            ];

            if (!is_null($this->id)) {
                $where[$this->table. '.id  !='] = $this->id;
            }

            $id = $this->readImproved([
                'what' => ['id'],
                'where' => $where
            ]);

            return !is_null($id);
        }

        /**
         * isVendorList
         *
         * Method checks is this list belong to this vendor
         *
         * @see AbstractCrud_model::readImproved
         * @return boolean
         */
        private function isVendorList(): bool
        {
            $id = $this->readImproved([
                'what' => [$this->table. '.id'],
                'where' => [
                    $this->table. '.vendorId' => $this->vendorId,
                    $this->table. '.id' => $this->id,
                ]
            ]);

            return !is_null($id);
        }

        /**
         * insertList
         *
         * Method inserts new list for vendor.
         *
         * $data = [
         *      'vendorId' => $vendorIdValue,   // mandatory
         *      'list' => $listValue,           // mandatory
         *      'active' => $active             // not mandatory default is 1
         * ]
         *
         * @access public
         * @see List_model::checkIsExists
         * @see AbstractSet_model::setObjectFromArray
         * @see AbstractCrud_model::create
         * @param array $data
         * @return boolean
         */
        public function insertList(array $data): bool
        {
            $this->setObjectFromArray($data);
            // first we check is list with name already exists for this vendor
            return $this->checkIsExists() ? false : $this->create();
        }

        /**
         * updateList
         *
         * Method updates list
         *
         * $data = [
         *      'vendorId' => $vendorIdValue,    // mandatory field
         *      'list' => $listValue             // not mandatory field
         *      'active' => $active              // not mandatory default is 1
         * ]
         * $this->id MUST be set. See $this->setObjectId($id)), it is defined in application/abstract/AbstractSet_model.php
         *
         *
         * @see List_model::checkIsListExists
         * @see List_model::isVendorList
         * @see AbstractSet_model::setObjectFromArray
         * @see AbstractCrud_model::update
         * @param integer $id
         * @param array $data
         * @return boolean
         */
        public function updateList(array $data): bool
        {
            $this->setObjectFromArray($data);
            return ($this->checkIsExists() || !$this->isVendorList()) ? false : $this->update();
        }


        /**
         * fetchVendorLists
         *
         * Method fetches all vendor's lists
         * $this->vendorId MUST be set. See $this->setProperty($key, $value), it is defined in application/abstract/AbstractSet_model.php
         *
         * @see AbstractCrud_model::readImproved
         * @return array|null
         */
        public function fetchVendorLists(): ?array
        {
            return $this->readImproved([
                'what' => [$this->table . '.*'],
                'where' => [
                    $this->table . '.vendorId' => $this->vendorId
                ]
            ]);
        }       

    }
