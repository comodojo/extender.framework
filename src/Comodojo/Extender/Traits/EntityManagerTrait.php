<?php namespace Comodojo\Extender\Traits;

use \Doctrine\ORM\EntityManager;

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

trait EntityManagerTrait {

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Get EntityManager
     *
     * @return EntityManager
     */
    public function getEntityManager() {

        return $this->em;

    }

    /**
     * Set EntityManager
     *
     * @param string $id
     * @return Schedule
     */
    public function setEntityManager(EntityManager $em) {

        $this->em = $em;

        return $this;

    }

}
