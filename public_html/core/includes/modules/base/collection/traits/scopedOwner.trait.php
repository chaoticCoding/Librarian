<?php

namespace core\base\collection\traits
{
    /** TODO - Most of this hasn't been built out yet as premissions/users/roles hasn't been finished but jumping point from an old project
     *
     * Trait scopedOwner, Common function for datatypes that have Poly-Ownership of Company,billingGroup,userGroup,User
     */
    trait scopedOwner
    {

        /** @var int */
        private $_ownerId;

        /** @var \core\base\collection\_item */
        private $_owner;

        /** @var string */
        private $_ownerName;

        /**
         * @param int $ownerId
         * @param int $scopedId
         *
         * @return \core\base\collection\_item|null
         *
         * @throws \Exception
         */
        function getScopedOwner ($ownerId, $scopedId = \util::SCOPE_USER)
        {
            $this->_owner = NULL;

            switch ($scopedId) {
                case \util::SCOPE_COMPANY:
                    $this->_owner = $this->getScopedOwner_ofCompany($ownerId);
                    break;

                case \util::SCOPE_BILLINGGROUP:
                    $this->_owner = $this->getScopedOwner_ofBillingGroup($ownerId);
                    break;

                case \util::SCOPE_USERGROUP:
                    $this->_owner = $this->getScopedOwner_ofUserGroup($ownerId);
                    break;

                case \util::SCOPE_USER:
                    $this->_owner = $this->getScopedOwner_ofUser($ownerId);
                    break;
            }

            return $this->_owner;
        }

        /**
         * @param     $ownerId
         * @param int $scopedId
         *
         * @return string|null
         *
         * @throws \Exception
         */
        function getScopedOwnerName_fromScope ($ownerId, $scopedId =\util::SCOPE_USER)
        {
            $owner = $this->getScopedOwner($ownerId, $scopedId);

            $this->_ownerName = NULL;

            switch (get_class($owner)) {
                case "company_collection_item":
                case "company_billingGroup_collection_item":
                case "company_userGroup_collection_item":
                    $this->_ownerName = $owner->label;
                    break;
                case "user_collection_item":
                    $this->_ownerName = $owner->firstName . " " . $owner->lastName;
                    break;
            }

            /*
            $this->_ownerName = null;

            switch ($scopedId) {
                case util::SCOPE_COMPANY:
                    $_owner = $this->getScopedOwner_ofCompany($ownerId);
                    $this->_ownerName = $_owner->label;
                    break;

                case util::SCOPE_BILLINGGROUP:
                    $_owner = $this->getScopedOwner_ofBillingGroup($ownerId);
                    $this->_ownerName = $_owner->label;
                    break;

                case util::SCOPE_USERGROUP:
                    $_owner = $this->getScopedOwner_ofUserGroup($ownerId);
                    $this->_ownerName = $_owner->label;
                    break;

                case util::SCOPE_USER:
                    $_owner = $this->getScopedOwner_ofUser($ownerId);
                    $this->_ownerName = $_owner->firstName . " " . $_owner->lastName;
                    break;
            }
            */

            return $this->_ownerName;
        }

        /**
         * @param $ownerId
         *
         * @return \user_collection_item
         *
         * @throws \Exception
         */
        function getScopedOwner_ofUser ($ownerId)
        {
            $user = \user::getUserItem();
            $user->load($ownerId);

            return $user;
        }

        /**
         * @param $ownerId
         *
         * @return \company_userGroup_collection_item
         *
         * @throws \Exception
         */
        function getScopedOwner_ofUserGroup ($ownerId)
        {
            $userGroup = \company::getCompanyUserGroupItem();
            $userGroup->load($ownerId);

            return $userGroup;
        }

        /**
         * @param $ownerId
         *
         * @return \company_billingGroup_collection_item
         *
         * @throws \Exception
         */
        function getScopedOwner_ofBillingGroup ($ownerId)
        {
            $billingGroup = \company::getCompanyBillingGroupItem();
            $billingGroup->load($ownerId);

            return $billingGroup;
        }

        /**
         * @param $ownerId
         *
         * @return \company_collection_item
         *
         * @throws \Exception
         */
        function getScopedOwner_ofCompany ($ownerId)
        {
            $company =\company::getCompanyItem();
            $company->load($ownerId);

            return $company;
        }
    }
}