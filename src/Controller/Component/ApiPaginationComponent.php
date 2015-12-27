<?php
namespace BryanCrowe\ApiPagination\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Event\Event;

/**
 * This is a simple component that injects pagination info into responses when
 * using CakePHP's PaginatorComponent alongside of CakePHP's JsonView or XmlView
 * classes.
 */
class ApiPaginationComponent extends Component
{
    /**
     * Default config.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'key' => 'pagination',
        'aliases' => [],
        'visible' => []
    ];

    /**
     * Holds the paging information.
     *
     * @var array
     */
    protected $pagingInfo = [];

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [];
    }

    /**
     * Injects the pagination info into the response if the current request is a
     * JSON or XML request with pagination.
     *
     * @param Event $event The Controller.beforeRender event.
     * @return void
     */
    public function beforeRender(Event $event)
    {
        $controller = $event->subject();

        if (!$this->isPaginatedApiRequest($controller)) {
            return;
        }

        $this->pagingInfo = $controller->request->params['paging'][$controller->name];
        $config = $this->config();

        if (!empty($config['aliases'])) {
            $this->setAliases();
        }

        if (!empty($config['visible'])) {
            $this->setVisibility();
        }

        $controller->set($config['key'], $this->pagingInfo);
        $controller->viewVars['_serialize'][] = $config['key'];
    }

    /**
     * Aliases the default pagination keys to the new keys that the user defines
     * in the config.
     *
     * @return void
     */
    protected function setAliases()
    {
        foreach ($this->config('aliases') as $key => $value) {
            $this->pagingInfo[$value] = $this->pagingInfo[$key];
            unset($this->pagingInfo[$key]);
        }
    }

    /**
     * Removes any pagination keys that haven't been defined as visible in the
     * config.
     *
     * @return void
     */
    protected function setVisibility()
    {
        $visible = $this->config('visible');
        foreach ($this->pagingInfo as $key => $value) {
            if (!in_array($key, $visible)) {
                unset($this->pagingInfo[$key]);
            }
        }
    }

    /**
     * Checks whether the current request is a JSON or XML request with
     * pagination.
     *
     * @param \Cake\Controller\Controller $controller A reference to the
     *   instantiating controller object
     * @return bool True if JSON or XML with paging, otherwise false.
     */
    protected function isPaginatedApiRequest(Controller $controller)
    {
        if (isset($controller->request->params['paging']) &&
            $controller->request->is(['json', 'xml'])
        ) {
            return true;
        }

        return false;
    }
}
