<?php namespace Comodojo\Extender\Socket\Messages\Scheduler;

use \Comodojo\Foundation\Validation\DataFilter;
use \Comodojo\Extender\Traits\BaseEntityTrait;
use \Cron\CronExpression;
use \Serializable;
use \Exception;

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

class Schedule implements Serializable {

    use BaseEntityTrait;

    protected $description;

    protected $enabled = false;

    protected $expression;

    public function getDescription() {

        return $this->description;

    }

    public function setDescription($description) {

        $this->description = $description;

        return $this;

    }

    public function getExpression() {

        return $this->expression;

    }

    public function setExpression($expression) {

        if ( !CronExpression::isValidExpression($expression) ) {
            throw new Exception("Invalid cron expression $expression");
        }

        $this->expression = $expression;

        return $this;

    }

    public function getEnabled() {

        return $this->enabled;

    }

    public function setEnabled($enabled) {

        $this->enabled =  DataFilter::filterBoolean($enabled);

        return $this;

    }

    public function import(array $data) {

        if ( !empty($data['id']) ) $this->setId($data['id']);
        if ( !empty($data['name']) ) $this->setName($data['name']);
        if ( !empty($data['description']) ) $this->setDescription($data['description']);
        if ( !empty($data['enabled']) ) $this->setEnabled($data['enabled']);
        if ( !empty($data['expression']) ) $this->setExpression($data['expression']);

    }

    public function export() {

        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'enabled' => $this->getEnabled(),
            'expression' => $this->getExpression()
        ];

    }

    public function serialize() {

        return serialize($this->export());

    }

    public function unserialize($data) {

        $data = unserialize($data);

        $this->import($data);

    }

    public static function create($name, $expression) {

        $s = new Schedule();

        return $s->setName($name)
            ->setExpression($expression);

    }

    public static function createFromExport(array $export) {

        $s = new Schedule();
        $s->import($export);

        return $s;

    }

}
