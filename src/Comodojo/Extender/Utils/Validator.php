<?php namespace Comodojo\Extender\Utils;

use \Cron\CronExpression;
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

class Validator {

    /**
     * Validate a cron expression and, if valid, return next run timestamp plus
     * an array of expression parts
     *
     * @param   string  $expression
     *
     * @return  array   Next run timestamp at first position, expression parts at second
     * @throws  \Exception
     */
    public static function cronExpression($expression) {

        try {

            $cron = CronExpression::factory($expression);

            $s = $cron->getNextRunDate()->format('c');

            $e = $cron->getExpression();

            $e_array = preg_split('/\s/', $e, -1, PREG_SPLIT_NO_EMPTY);

            $e_count = count($e_array);

            if ( $e_count < 5 || $e_count > 6 ) throw new Exception($e." is not a valid cron expression");

            if ( $e_count == 5 ) $e_array[] = "*";

        }
        catch (Exception $e) {

            throw $e;

        }

        return array($s, $e_array);

    }

    public static function laggerTimeout($timeout) {

        return filter_var($timeout, FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 5,
                'min_range' => 0
            )
        ));

    }

    public static function maxChildRuntime($runtime) {

        return filter_var($runtime, FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 600,
                'min_range' => 1
            )
        ));

    }

    public static function forkLimit($limit) {

        return filter_var($limit, FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 0,
                'min_range' => 0
            )
        ));

    }

    public static function multithread($multithread) {

        return $multithread === true && Checks::multithread();

    }

}
