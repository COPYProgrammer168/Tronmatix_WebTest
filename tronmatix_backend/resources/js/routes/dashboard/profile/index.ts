import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
import avatar from './avatar'
/**
* @see \App\Http\Controllers\Dashboard\ProfileController::update
 * @see app/Http/Controllers/Dashboard/ProfileController.php:23
 * @route '/dashboard/profile'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post","put"],
    url: '/dashboard/profile',
} satisfies RouteDefinition<["post","put"]>

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::update
 * @see app/Http/Controllers/Dashboard/ProfileController.php:23
 * @route '/dashboard/profile'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::update
 * @see app/Http/Controllers/Dashboard/ProfileController.php:23
 * @route '/dashboard/profile'
 */
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})
/**
* @see \App\Http\Controllers\Dashboard\ProfileController::update
 * @see app/Http/Controllers/Dashboard/ProfileController.php:23
 * @route '/dashboard/profile'
 */
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\ProfileController::update
 * @see app/Http/Controllers/Dashboard/ProfileController.php:23
 * @route '/dashboard/profile'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\ProfileController::update
 * @see app/Http/Controllers/Dashboard/ProfileController.php:23
 * @route '/dashboard/profile'
 */
        updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(options),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Dashboard\ProfileController::update
 * @see app/Http/Controllers/Dashboard/ProfileController.php:23
 * @route '/dashboard/profile'
 */
        updateForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Dashboard\ProfileController::password
 * @see app/Http/Controllers/Dashboard/ProfileController.php:64
 * @route '/dashboard/profile/password'
 */
export const password = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: password.url(options),
    method: 'put',
})

password.definition = {
    methods: ["put"],
    url: '/dashboard/profile/password',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::password
 * @see app/Http/Controllers/Dashboard/ProfileController.php:64
 * @route '/dashboard/profile/password'
 */
password.url = (options?: RouteQueryOptions) => {
    return password.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::password
 * @see app/Http/Controllers/Dashboard/ProfileController.php:64
 * @route '/dashboard/profile/password'
 */
password.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: password.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\ProfileController::password
 * @see app/Http/Controllers/Dashboard/ProfileController.php:64
 * @route '/dashboard/profile/password'
 */
    const passwordForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: password.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\ProfileController::password
 * @see app/Http/Controllers/Dashboard/ProfileController.php:64
 * @route '/dashboard/profile/password'
 */
        passwordForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: password.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    password.form = passwordForm
/**
* @see \App\Http\Controllers\Dashboard\ProfileController::role
 * @see app/Http/Controllers/Dashboard/ProfileController.php:84
 * @route '/dashboard/profile/role'
 */
export const role = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: role.url(options),
    method: 'put',
})

role.definition = {
    methods: ["put"],
    url: '/dashboard/profile/role',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::role
 * @see app/Http/Controllers/Dashboard/ProfileController.php:84
 * @route '/dashboard/profile/role'
 */
role.url = (options?: RouteQueryOptions) => {
    return role.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::role
 * @see app/Http/Controllers/Dashboard/ProfileController.php:84
 * @route '/dashboard/profile/role'
 */
role.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: role.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\ProfileController::role
 * @see app/Http/Controllers/Dashboard/ProfileController.php:84
 * @route '/dashboard/profile/role'
 */
    const roleForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: role.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\ProfileController::role
 * @see app/Http/Controllers/Dashboard/ProfileController.php:84
 * @route '/dashboard/profile/role'
 */
        roleForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: role.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    role.form = roleForm
const profile = {
    update: Object.assign(update, update),
password: Object.assign(password, password),
role: Object.assign(role, role),
avatar: Object.assign(avatar, avatar),
}

export default profile