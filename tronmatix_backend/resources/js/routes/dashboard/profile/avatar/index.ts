import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\ProfileController::remove
 * @see app/Http/Controllers/Dashboard/ProfileController.php:52
 * @route '/dashboard/profile/avatar'
 */
export const remove = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: remove.url(options),
    method: 'delete',
})

remove.definition = {
    methods: ["delete"],
    url: '/dashboard/profile/avatar',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::remove
 * @see app/Http/Controllers/Dashboard/ProfileController.php:52
 * @route '/dashboard/profile/avatar'
 */
remove.url = (options?: RouteQueryOptions) => {
    return remove.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::remove
 * @see app/Http/Controllers/Dashboard/ProfileController.php:52
 * @route '/dashboard/profile/avatar'
 */
remove.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: remove.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\ProfileController::remove
 * @see app/Http/Controllers/Dashboard/ProfileController.php:52
 * @route '/dashboard/profile/avatar'
 */
    const removeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: remove.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\ProfileController::remove
 * @see app/Http/Controllers/Dashboard/ProfileController.php:52
 * @route '/dashboard/profile/avatar'
 */
        removeForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: remove.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    remove.form = removeForm
const avatar = {
    remove: Object.assign(remove, remove),
}

export default avatar