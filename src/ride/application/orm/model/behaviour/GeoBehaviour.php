<?php

namespace ride\application\orm\model\behaviour;

use ride\library\orm\entry\GeoEntry;
use ride\library\orm\model\behaviour\AbstractBehaviour;
use ride\library\orm\model\Model;
use ride\library\validation\exception\ValidationException;

/**
 * Behaviour to resolve the coordinates of a location
 */
class GeoBehaviour extends AbstractBehaviour {

    /**
     * Name of the service
     * @var string
     */
    protected $geocoderService;

    /**
     * Constructs a new behaviour
     * @param string $geocoderService Name of the service inside the geocoder
     * @return null
     */
    public function __construct($geocoderService) {
        $this->geocoderService = $geocoderService;
    }

    /**
     * Hook before validation of an entry
     * @param \ride\library\orm\model\Model $model
     * @param mixed $entry
     * @param \ride\library\validation\exception\ValidationException $exception
     * @return null
     */
    public function preValidate(Model $model, $entry, ValidationException $exception) {
        if (!$entry instanceof GeoEntry) {
            return;
        }

        $address = $entry->getGeoAddress();
        if (!$address) {
            return;
        }

        $geocoder = $model->getOrmManager()->getDependencyInjector()->get('ride\\library\\geocode\\Geocoder');

        $geocodeResults = $geocoder->geocode($this->geocoderService, $address);
        foreach ($geocodeResults as $geocodeResult) {
            $coordinate = $geocodeResult->getCoordinate();

            $entry->setLatitude($coordinate->getLatitude());
            $entry->setLongitude($coordinate->getLongitude());

            break;
        }
    }

}
