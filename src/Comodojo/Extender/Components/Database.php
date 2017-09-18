<?php namespace Comodojo\Extender\Components;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Base\ConfigurationTrait;
use \Doctrine\ORM\Tools\Setup;
use \Doctrine\ORM\Tools\SchemaValidator;
use \Doctrine\ORM\EntityManager;
use \Doctrine\DBAL\DriverManager;
use \Doctrine\DBAL\Configuration as DoctrineConfiguration;
use \Doctrine\ORM\Query\ResultSetMappingBuilder;
use \Doctrine\Common\Cache\ApcCache;

/**
 * @package     Comodojo Extender
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @license     MIT
 *
 * LICENSE:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class Database {

    use ConfigurationTrait;

    protected $entity_manager;

    public function __construct(Configuration $configuration) {

        $this->setConfiguration($configuration);

    }

    public function getConnection() {

        $configuration = $this->getConfiguration();
        $connection_params = self::getConnectionParameters($configuration);
        $driverConfig = new DoctrineConfiguration();

        $devmode = $configuration->get('database-devmode') === true ? true : false;

        // currently only ApcCache driver is supported
        if ( empty($devmode) ) {

            $cache = new ApcCache();
            $driverConfig->setResultCacheImpl($cache);

        }

        return DriverManager::getConnection($connection_params, $driverConfig);

    }

    public function getEntityManager() {

        if ( $this->entity_manager === null ) {

            $configuration = $this->getConfiguration();

            $base_folder = $configuration->get('base-path');

            $connection_params = self::getConnectionParameters($configuration);

            $entity_repositories = self::getEntityRepositories($configuration);

            $proxies_folder = self::getProxiesFolder($configuration);

            $devmode = $configuration->get('database-devmode') === true ? true : false;

            $metadata_mode = $configuration->get('database-metadata');

            $config_args = [
                $entity_repositories,
                $devmode,
                $proxies_folder,
                null,
                false
            ];

            switch (strtoupper($metadata_mode)) {

                case 'YAML':
                    $db_config = Setup::createYAMLMetadataConfiguration(...$config_args);
                    break;

                case 'XML':
                    $db_config = Setup::createXMLMetadataConfiguration(...$config_args);
                    break;

                case 'ANNOTATIONS':
                default:
                    $db_config = Setup::createAnnotationMetadataConfiguration(...$config_args);
                    break;
            }

            if ( $devmode ) {

                $db_config->setAutoGenerateProxyClasses(true);

            } else {

                $cache = new ApcCache();
                $db_config->setAutoGenerateProxyClasses(false);
                $db_config->setQueryCacheImpl($cache);
                $db_config->setResultCacheImpl($cache);
                $db_config->setMetadataCacheImpl($cache);

            }

            $this->entity_manager = EntityManager::create($connection_params, $db_config);

        } else {

            $this->entity_manager->clear();

        }

        return $this->entity_manager;

    }

    public function setEntityManager(EntityManager $manager) {

        $this->entity_manager = $manager;

        return $this;

    }

    public static function init(Configuration $configuration) {

        return new Database($configuration);

    }

    public static function validate(Configuration $configuration) {

        $db = new Database($configuration);

        $em = $db->getEntityManager();
        $validator = new SchemaValidator($em);

        $result = [
            'MAPPING' => empty($validator->validateMapping()),
            'SYNC' => $validator->schemaInSyncWithMetadata()
        ];

        unset($validator);
        $em->getConnection()->close();
        $em->close();
        unset($db);

        return $result;

    }

    protected static function getConnectionParameters(Configuration $configuration) {

        $connection_params = $configuration->get('database-params');
        $base_folder = $configuration->get('base-path');

        if ( isset($connection_params['path']) ) $connection_params['path'] = $base_folder."/".$connection_params['path'];

        return $connection_params;

    }

    protected static function getEntityRepositories(Configuration $configuration) {

        $base_folder = $configuration->get('base-path');
        $repos = $configuration->get('database-repositories');

        return array_map(function($repo) use ($base_folder) {
            return substr($repo, 0, 1 ) === "/" ? $repo : "$base_folder/$repo";
        }, $repos);

    }

    protected static function getProxiesFolder(Configuration $configuration) {

        $base_folder = $configuration->get('base-path');
        $folder = $configuration->get('database-proxies');

        return substr($folder, 0, 1 ) === "/" ? $folder : "$base_folder/$folder";

    }

}
