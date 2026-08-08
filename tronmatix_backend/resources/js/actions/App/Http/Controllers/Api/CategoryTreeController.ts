import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\CategoryTreeController::tree
 * @see app/Http/Controllers/Api/CategoryTreeController.php:17
 * @route '/api/categories/tree'
 */
export const tree = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tree.url(options),
    method: 'get',
})

tree.definition = {
    methods: ["get","head"],
    url: '/api/categories/tree',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\CategoryTreeController::tree
 * @see app/Http/Controllers/Api/CategoryTreeController.php:17
 * @route '/api/categories/tree'
 */
tree.url = (options?: RouteQueryOptions) => {
    return tree.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\CategoryTreeController::tree
 * @see app/Http/Controllers/Api/CategoryTreeController.php:17
 * @route '/api/categories/tree'
 */
tree.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tree.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\CategoryTreeController::tree
 * @see app/Http/Controllers/Api/CategoryTreeController.php:17
 * @route '/api/categories/tree'
 */
tree.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: tree.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\CategoryTreeController::tree
 * @see app/Http/Controllers/Api/CategoryTreeController.php:17
 * @route '/api/categories/tree'
 */
    const treeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: tree.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\CategoryTreeController::tree
 * @see app/Http/Controllers/Api/CategoryTreeController.php:17
 * @route '/api/categories/tree'
 */
        treeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: tree.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\CategoryTreeController::tree
 * @see app/Http/Controllers/Api/CategoryTreeController.php:17
 * @route '/api/categories/tree'
 */
        treeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: tree.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    tree.form = treeForm
const CategoryTreeController = { tree }

export default CategoryTreeController