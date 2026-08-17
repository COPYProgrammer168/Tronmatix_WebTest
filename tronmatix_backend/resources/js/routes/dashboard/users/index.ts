import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\UserController::role
 * @see app/Http/Controllers/Dashboard/UserController.php:75
 * @route '/dashboard/users/{user}/role'
 */
export const role = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: role.url(args, options),
    method: 'put',
})

role.definition = {
    methods: ["put"],
    url: '/dashboard/users/{user}/role',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\UserController::role
 * @see app/Http/Controllers/Dashboard/UserController.php:75
 * @route '/dashboard/users/{user}/role'
 */
role.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { user: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.id
                : args.user,
                }

    return role.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\UserController::role
 * @see app/Http/Controllers/Dashboard/UserController.php:75
 * @route '/dashboard/users/{user}/role'
 */
role.put = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: role.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\UserController::role
 * @see app/Http/Controllers/Dashboard/UserController.php:75
 * @route '/dashboard/users/{user}/role'
 */
    const roleForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: role.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\UserController::role
 * @see app/Http/Controllers/Dashboard/UserController.php:75
 * @route '/dashboard/users/{user}/role'
 */
        roleForm.put = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: role.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    role.form = roleForm
const users = {
    role: Object.assign(role, role),
}

export default users