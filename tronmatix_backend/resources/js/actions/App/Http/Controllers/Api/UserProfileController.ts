import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\UserProfileController::show
 * @see app/Http/Controllers/Api/UserProfileController.php:24
 * @route '/api/user/profile'
 */
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/user/profile',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\UserProfileController::show
 * @see app/Http/Controllers/Api/UserProfileController.php:24
 * @route '/api/user/profile'
 */
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\UserProfileController::show
 * @see app/Http/Controllers/Api/UserProfileController.php:24
 * @route '/api/user/profile'
 */
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\UserProfileController::show
 * @see app/Http/Controllers/Api/UserProfileController.php:24
 * @route '/api/user/profile'
 */
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\UserProfileController::show
 * @see app/Http/Controllers/Api/UserProfileController.php:24
 * @route '/api/user/profile'
 */
    const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\UserProfileController::show
 * @see app/Http/Controllers/Api/UserProfileController.php:24
 * @route '/api/user/profile'
 */
        showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\UserProfileController::show
 * @see app/Http/Controllers/Api/UserProfileController.php:24
 * @route '/api/user/profile'
 */
        showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Api\UserProfileController::update
 * @see app/Http/Controllers/Api/UserProfileController.php:33
 * @route '/api/user/profile'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/api/user/profile',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Api\UserProfileController::update
 * @see app/Http/Controllers/Api/UserProfileController.php:33
 * @route '/api/user/profile'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\UserProfileController::update
 * @see app/Http/Controllers/Api/UserProfileController.php:33
 * @route '/api/user/profile'
 */
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Api\UserProfileController::update
 * @see app/Http/Controllers/Api/UserProfileController.php:33
 * @route '/api/user/profile'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\UserProfileController::update
 * @see app/Http/Controllers/Api/UserProfileController.php:33
 * @route '/api/user/profile'
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
* @see \App\Http\Controllers\Api\UserProfileController::completeProfile
 * @see app/Http/Controllers/Api/UserProfileController.php:57
 * @route '/api/user/profile/complete'
 */
export const completeProfile = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completeProfile.url(options),
    method: 'post',
})

completeProfile.definition = {
    methods: ["post"],
    url: '/api/user/profile/complete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\UserProfileController::completeProfile
 * @see app/Http/Controllers/Api/UserProfileController.php:57
 * @route '/api/user/profile/complete'
 */
completeProfile.url = (options?: RouteQueryOptions) => {
    return completeProfile.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\UserProfileController::completeProfile
 * @see app/Http/Controllers/Api/UserProfileController.php:57
 * @route '/api/user/profile/complete'
 */
completeProfile.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completeProfile.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\UserProfileController::completeProfile
 * @see app/Http/Controllers/Api/UserProfileController.php:57
 * @route '/api/user/profile/complete'
 */
    const completeProfileForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: completeProfile.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\UserProfileController::completeProfile
 * @see app/Http/Controllers/Api/UserProfileController.php:57
 * @route '/api/user/profile/complete'
 */
        completeProfileForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: completeProfile.url(options),
            method: 'post',
        })
    
    completeProfile.form = completeProfileForm
/**
* @see \App\Http\Controllers\Api\UserProfileController::stats
 * @see app/Http/Controllers/Api/UserProfileController.php:207
 * @route '/api/user/stats'
 */
export const stats = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})

stats.definition = {
    methods: ["get","head"],
    url: '/api/user/stats',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\UserProfileController::stats
 * @see app/Http/Controllers/Api/UserProfileController.php:207
 * @route '/api/user/stats'
 */
stats.url = (options?: RouteQueryOptions) => {
    return stats.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\UserProfileController::stats
 * @see app/Http/Controllers/Api/UserProfileController.php:207
 * @route '/api/user/stats'
 */
stats.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\UserProfileController::stats
 * @see app/Http/Controllers/Api/UserProfileController.php:207
 * @route '/api/user/stats'
 */
stats.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: stats.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\UserProfileController::stats
 * @see app/Http/Controllers/Api/UserProfileController.php:207
 * @route '/api/user/stats'
 */
    const statsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: stats.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\UserProfileController::stats
 * @see app/Http/Controllers/Api/UserProfileController.php:207
 * @route '/api/user/stats'
 */
        statsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: stats.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\UserProfileController::stats
 * @see app/Http/Controllers/Api/UserProfileController.php:207
 * @route '/api/user/stats'
 */
        statsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: stats.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    stats.form = statsForm
/**
* @see \App\Http\Controllers\Api\UserProfileController::uploadAvatar
 * @see app/Http/Controllers/Api/UserProfileController.php:79
 * @route '/api/user/avatar'
 */
export const uploadAvatar = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadAvatar.url(options),
    method: 'post',
})

uploadAvatar.definition = {
    methods: ["post"],
    url: '/api/user/avatar',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\UserProfileController::uploadAvatar
 * @see app/Http/Controllers/Api/UserProfileController.php:79
 * @route '/api/user/avatar'
 */
uploadAvatar.url = (options?: RouteQueryOptions) => {
    return uploadAvatar.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\UserProfileController::uploadAvatar
 * @see app/Http/Controllers/Api/UserProfileController.php:79
 * @route '/api/user/avatar'
 */
uploadAvatar.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadAvatar.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\UserProfileController::uploadAvatar
 * @see app/Http/Controllers/Api/UserProfileController.php:79
 * @route '/api/user/avatar'
 */
    const uploadAvatarForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: uploadAvatar.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\UserProfileController::uploadAvatar
 * @see app/Http/Controllers/Api/UserProfileController.php:79
 * @route '/api/user/avatar'
 */
        uploadAvatarForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: uploadAvatar.url(options),
            method: 'post',
        })
    
    uploadAvatar.form = uploadAvatarForm
/**
* @see \App\Http\Controllers\Api\UserProfileController::removeAvatar
 * @see app/Http/Controllers/Api/UserProfileController.php:101
 * @route '/api/user/avatar'
 */
export const removeAvatar = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: removeAvatar.url(options),
    method: 'delete',
})

removeAvatar.definition = {
    methods: ["delete"],
    url: '/api/user/avatar',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\UserProfileController::removeAvatar
 * @see app/Http/Controllers/Api/UserProfileController.php:101
 * @route '/api/user/avatar'
 */
removeAvatar.url = (options?: RouteQueryOptions) => {
    return removeAvatar.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\UserProfileController::removeAvatar
 * @see app/Http/Controllers/Api/UserProfileController.php:101
 * @route '/api/user/avatar'
 */
removeAvatar.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: removeAvatar.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\UserProfileController::removeAvatar
 * @see app/Http/Controllers/Api/UserProfileController.php:101
 * @route '/api/user/avatar'
 */
    const removeAvatarForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: removeAvatar.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\UserProfileController::removeAvatar
 * @see app/Http/Controllers/Api/UserProfileController.php:101
 * @route '/api/user/avatar'
 */
        removeAvatarForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: removeAvatar.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    removeAvatar.form = removeAvatarForm
/**
* @see \App\Http\Controllers\Api\UserProfileController::locations
 * @see app/Http/Controllers/Api/UserProfileController.php:115
 * @route '/api/user/locations'
 */
export const locations = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: locations.url(options),
    method: 'get',
})

locations.definition = {
    methods: ["get","head"],
    url: '/api/user/locations',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\UserProfileController::locations
 * @see app/Http/Controllers/Api/UserProfileController.php:115
 * @route '/api/user/locations'
 */
locations.url = (options?: RouteQueryOptions) => {
    return locations.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\UserProfileController::locations
 * @see app/Http/Controllers/Api/UserProfileController.php:115
 * @route '/api/user/locations'
 */
locations.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: locations.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\UserProfileController::locations
 * @see app/Http/Controllers/Api/UserProfileController.php:115
 * @route '/api/user/locations'
 */
locations.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: locations.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\UserProfileController::locations
 * @see app/Http/Controllers/Api/UserProfileController.php:115
 * @route '/api/user/locations'
 */
    const locationsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: locations.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\UserProfileController::locations
 * @see app/Http/Controllers/Api/UserProfileController.php:115
 * @route '/api/user/locations'
 */
        locationsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: locations.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\UserProfileController::locations
 * @see app/Http/Controllers/Api/UserProfileController.php:115
 * @route '/api/user/locations'
 */
        locationsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: locations.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    locations.form = locationsForm
/**
* @see \App\Http\Controllers\Api\UserProfileController::storeLocation
 * @see app/Http/Controllers/Api/UserProfileController.php:126
 * @route '/api/user/locations'
 */
export const storeLocation = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeLocation.url(options),
    method: 'post',
})

storeLocation.definition = {
    methods: ["post"],
    url: '/api/user/locations',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\UserProfileController::storeLocation
 * @see app/Http/Controllers/Api/UserProfileController.php:126
 * @route '/api/user/locations'
 */
storeLocation.url = (options?: RouteQueryOptions) => {
    return storeLocation.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\UserProfileController::storeLocation
 * @see app/Http/Controllers/Api/UserProfileController.php:126
 * @route '/api/user/locations'
 */
storeLocation.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeLocation.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\UserProfileController::storeLocation
 * @see app/Http/Controllers/Api/UserProfileController.php:126
 * @route '/api/user/locations'
 */
    const storeLocationForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeLocation.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\UserProfileController::storeLocation
 * @see app/Http/Controllers/Api/UserProfileController.php:126
 * @route '/api/user/locations'
 */
        storeLocationForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeLocation.url(options),
            method: 'post',
        })
    
    storeLocation.form = storeLocationForm
/**
* @see \App\Http\Controllers\Api\UserProfileController::updateLocation
 * @see app/Http/Controllers/Api/UserProfileController.php:165
 * @route '/api/user/locations/{id}'
 */
export const updateLocation = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateLocation.url(args, options),
    method: 'put',
})

updateLocation.definition = {
    methods: ["put"],
    url: '/api/user/locations/{id}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Api\UserProfileController::updateLocation
 * @see app/Http/Controllers/Api/UserProfileController.php:165
 * @route '/api/user/locations/{id}'
 */
updateLocation.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return updateLocation.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\UserProfileController::updateLocation
 * @see app/Http/Controllers/Api/UserProfileController.php:165
 * @route '/api/user/locations/{id}'
 */
updateLocation.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateLocation.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Api\UserProfileController::updateLocation
 * @see app/Http/Controllers/Api/UserProfileController.php:165
 * @route '/api/user/locations/{id}'
 */
    const updateLocationForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateLocation.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\UserProfileController::updateLocation
 * @see app/Http/Controllers/Api/UserProfileController.php:165
 * @route '/api/user/locations/{id}'
 */
        updateLocationForm.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateLocation.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateLocation.form = updateLocationForm
/**
* @see \App\Http\Controllers\Api\UserProfileController::destroyLocation
 * @see app/Http/Controllers/Api/UserProfileController.php:192
 * @route '/api/user/locations/{id}'
 */
export const destroyLocation = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyLocation.url(args, options),
    method: 'delete',
})

destroyLocation.definition = {
    methods: ["delete"],
    url: '/api/user/locations/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\UserProfileController::destroyLocation
 * @see app/Http/Controllers/Api/UserProfileController.php:192
 * @route '/api/user/locations/{id}'
 */
destroyLocation.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return destroyLocation.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\UserProfileController::destroyLocation
 * @see app/Http/Controllers/Api/UserProfileController.php:192
 * @route '/api/user/locations/{id}'
 */
destroyLocation.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyLocation.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\UserProfileController::destroyLocation
 * @see app/Http/Controllers/Api/UserProfileController.php:192
 * @route '/api/user/locations/{id}'
 */
    const destroyLocationForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyLocation.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\UserProfileController::destroyLocation
 * @see app/Http/Controllers/Api/UserProfileController.php:192
 * @route '/api/user/locations/{id}'
 */
        destroyLocationForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyLocation.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyLocation.form = destroyLocationForm
const UserProfileController = { show, update, completeProfile, stats, uploadAvatar, removeAvatar, locations, storeLocation, updateLocation, destroyLocation }

export default UserProfileController