<?php namespace Comodojo\Extender\Socket\Messages\Worklog;

use \Comodojo\Foundation\Validation\DataFilter;
use \Serializable;

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

class Filter implements Serializable {

    protected $limit = 10;

    protected $offset = 0;

    protected $reverse = false;

    public function getLimit() {

        return $this->limit;

    }

    public function getOffset() {

        return $this->offset;

    }

    public function getReverse() {

        return $this->reverse;

    }

    public function setLimit($limit) {

        $this->limit = DataFilter::filterInteger($limit, 1, 1000, 10);

        return $this;

    }

    public function setOffset($offset) {

        $this->offset = DataFilter::filterInteger($offset, 0);

        return $this;

    }

    public function setReverse($reverse) {

        $this->reverse = DataFilter::filterBoolean($reverse);

        return $this;

    }

    public function export() {

        return [
            'limit' => $this->getLimit(),
            'offset' => $this->getOffset(),
            'reverse' => $this->getReverse()
        ];

    }

    public function import(array $data) {

        if ( !empty($data['limit']) ) $this->setLimit($data['limit']);
        if ( !empty($data['offset']) ) $this->setOffset($data['offset']);
        if ( !empty($data['reverse']) ) $this->setReverse($data['reverse']);
    }

    public function serialize() {

        return serialize($this->export());

    }

    public function unserialize($data) {

        $data = unserialize($data);

        $this->import($data);

    }

    public static function create() {

        return new Filter();

    }

    public static function createFromExport(array $export) {

        $f = new Filter();
        $f->import($export);

        return $f;

    }

}
