<?php namespace Comodojo\Extender\Traits;

use \Comodojo\Extender\Task\Table as TasksTable;

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
 
trait TasksTableTrait {

    /**
     * @var TasksTable
     */
    protected $table;

    /**
     * Get current TasksTable
     *
     * @return TasksTable
     */
    public function getTasksTable() {

        return $this->table;

    }

    /**
     * Set current TasksTable
     *
     * @param TasksTable $table
     * @return self
     */
    public function setTasksTable(TasksTable $table) {

        $this->table = $table;

        return $this;

    }

}
