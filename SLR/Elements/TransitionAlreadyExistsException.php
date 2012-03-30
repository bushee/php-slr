<?php
/**
 * TransitionAlreadyExistsException exception.
 *
 * PHP version 5.2.todo
 *
 * @category Exceptions
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */

/**
 * Exception for when there was a trial to add transition possibility for token
 * that there is already a known transition for in given state.
 *
 * @category Exceptions
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */
class SLR_Elements_TransitionAlreadyExistsException extends Exception
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
     * @var SLR_Elements_State $state
     */
    protected $state;

    /**
     * Creates new transition already exists exception.
     *
     * @param string             $token    erroneous token name
     * @param SLR_Elements_State $state    state for which exception occured
     * @param int                $code     the exception code
     * @param Exception          $previous the previous exception used for the
     *                                     exception chaining
     */
    public function __construct(
        $token, $state, $code = 0, $previous = null
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
     * @return SLR_Elements_State
     */
    public function getState()
    {
        return $this->state;
    }
}