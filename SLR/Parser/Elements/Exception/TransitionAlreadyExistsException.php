<?php
/**
 * TransitionAlreadyExistsException exception.
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  SLR\Parser\Elements\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Parser\Elements\Exception;

/**
 * Exception for when there was a trial to add transition possibility for token
 * that there is already a known transition for in given state.
 *
 * @category SLR
 * @package  SLR\Parser\Elements\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
use SLR\Parser\Elements\State;

class TransitionAlreadyExistsException extends \Exception
{
    /**
     * Erroneous token.
     *
     * @var string $token
     */
    protected $token;
    /**
     * State in which erroneous transition add was attempted.
     *
     * @var State $state
     */
    protected $state;

    /**
     * Creates new transition already exists exception.
     *
     * @param string     $token    Erroneous token name
     * @param State      $state    State for which exception occured
     * @param int        $code     Exception code
     * @param \Exception $previous Previous exception used for exception chaining
     */
    public function __construct(
        $token, State $state, $code = 0, \Exception $previous = null
    ) {
        parent::__construct(
            'State ' . $state->getId()
            . " already has a transition for token '$token'.",
            $code, $previous
        );
        $this->token = $token;
        $this->state = $state;
    }

    /**
     * Returns erroneous token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Returns state for which exception occured.
     *
     * @return State
     */
    public function getState()
    {
        return $this->state;
    }
}