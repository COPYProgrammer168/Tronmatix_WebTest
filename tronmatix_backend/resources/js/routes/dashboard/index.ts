import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import loginDf2c2a from './login'
import register702019 from './register'
import requestAccessD72a72 from './request-access'
import products237d17 from './products'
import ordersB47e5f from './orders'
import users48860f from './users'
import discounts5ba67e from './discounts'
import bannersEe4e07 from './banners'
import videos3b3162 from './videos'
import profile937a89 from './profile'
import settings69f00b from './settings'
import deliveryProviders from './delivery-providers'
import staffC58c8e from './staff'
import admin from './admin'
import staffRequests from './staff-requests'
import telegram from './telegram'
/**
* @see \App\Http\Controllers\AdminAuthController::login
 * @see app/Http/Controllers/AdminAuthController.php:17
 * @route '/dashboard/login'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})

login.definition = {
    methods: ["get","head"],
    url: '/dashboard/login',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminAuthController::login
 * @see app/Http/Controllers/AdminAuthController.php:17
 * @route '/dashboard/login'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminAuthController::login
 * @see app/Http/Controllers/AdminAuthController.php:17
 * @route '/dashboard/login'
 */
login.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminAuthController::login
 * @see app/Http/Controllers/AdminAuthController.php:17
 * @route '/dashboard/login'
 */
login.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: login.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminAuthController::login
 * @see app/Http/Controllers/AdminAuthController.php:17
 * @route '/dashboard/login'
 */
    const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: login.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminAuthController::login
 * @see app/Http/Controllers/AdminAuthController.php:17
 * @route '/dashboard/login'
 */
        loginForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: login.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminAuthController::login
 * @see app/Http/Controllers/AdminAuthController.php:17
 * @route '/dashboard/login'
 */
        loginForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: login.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    login.form = loginForm
/**
* @see \App\Http\Controllers\AdminAuthController::register
 * @see app/Http/Controllers/AdminAuthController.php:129
 * @route '/dashboard/register'
 */
export const register = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
})

register.definition = {
    methods: ["get","head"],
    url: '/dashboard/register',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminAuthController::register
 * @see app/Http/Controllers/AdminAuthController.php:129
 * @route '/dashboard/register'
 */
register.url = (options?: RouteQueryOptions) => {
    return register.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminAuthController::register
 * @see app/Http/Controllers/AdminAuthController.php:129
 * @route '/dashboard/register'
 */
register.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminAuthController::register
 * @see app/Http/Controllers/AdminAuthController.php:129
 * @route '/dashboard/register'
 */
register.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: register.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminAuthController::register
 * @see app/Http/Controllers/AdminAuthController.php:129
 * @route '/dashboard/register'
 */
    const registerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: register.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminAuthController::register
 * @see app/Http/Controllers/AdminAuthController.php:129
 * @route '/dashboard/register'
 */
        registerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: register.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminAuthController::register
 * @see app/Http/Controllers/AdminAuthController.php:129
 * @route '/dashboard/register'
 */
        registerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: register.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    register.form = registerForm
/**
* @see \App\Http\Controllers\DashboardController::index
 * @see app/Http/Controllers/DashboardController.php:30
 * @route '/dashboard'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::index
 * @see app/Http/Controllers/DashboardController.php:30
 * @route '/dashboard'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::index
 * @see app/Http/Controllers/DashboardController.php:30
 * @route '/dashboard'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::index
 * @see app/Http/Controllers/DashboardController.php:30
 * @route '/dashboard'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::index
 * @see app/Http/Controllers/DashboardController.php:30
 * @route '/dashboard'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::index
 * @see app/Http/Controllers/DashboardController.php:30
 * @route '/dashboard'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::index
 * @see app/Http/Controllers/DashboardController.php:30
 * @route '/dashboard'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\DashboardController::exportMethod
 * @see app/Http/Controllers/DashboardController.php:677
 * @route '/dashboard/export'
 */
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/dashboard/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::exportMethod
 * @see app/Http/Controllers/DashboardController.php:677
 * @route '/dashboard/export'
 */
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::exportMethod
 * @see app/Http/Controllers/DashboardController.php:677
 * @route '/dashboard/export'
 */
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::exportMethod
 * @see app/Http/Controllers/DashboardController.php:677
 * @route '/dashboard/export'
 */
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::exportMethod
 * @see app/Http/Controllers/DashboardController.php:677
 * @route '/dashboard/export'
 */
    const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMethod.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::exportMethod
 * @see app/Http/Controllers/DashboardController.php:677
 * @route '/dashboard/export'
 */
        exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::exportMethod
 * @see app/Http/Controllers/DashboardController.php:677
 * @route '/dashboard/export'
 */
        exportMethodForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportMethod.form = exportMethodForm
/**
* @see \App\Http\Controllers\AdminAuthController::logout
 * @see app/Http/Controllers/AdminAuthController.php:172
 * @route '/dashboard/logout'
 */
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: '/dashboard/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminAuthController::logout
 * @see app/Http/Controllers/AdminAuthController.php:172
 * @route '/dashboard/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminAuthController::logout
 * @see app/Http/Controllers/AdminAuthController.php:172
 * @route '/dashboard/logout'
 */
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AdminAuthController::logout
 * @see app/Http/Controllers/AdminAuthController.php:172
 * @route '/dashboard/logout'
 */
    const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: logout.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AdminAuthController::logout
 * @see app/Http/Controllers/AdminAuthController.php:172
 * @route '/dashboard/logout'
 */
        logoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: logout.url(options),
            method: 'post',
        })
    
    logout.form = logoutForm
/**
* @see \App\Http\Controllers\StaffRequestController::requestAccess
 * @see app/Http/Controllers/StaffRequestController.php:20
 * @route '/dashboard/request-access'
 */
export const requestAccess = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: requestAccess.url(options),
    method: 'get',
})

requestAccess.definition = {
    methods: ["get","head"],
    url: '/dashboard/request-access',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\StaffRequestController::requestAccess
 * @see app/Http/Controllers/StaffRequestController.php:20
 * @route '/dashboard/request-access'
 */
requestAccess.url = (options?: RouteQueryOptions) => {
    return requestAccess.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\StaffRequestController::requestAccess
 * @see app/Http/Controllers/StaffRequestController.php:20
 * @route '/dashboard/request-access'
 */
requestAccess.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: requestAccess.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\StaffRequestController::requestAccess
 * @see app/Http/Controllers/StaffRequestController.php:20
 * @route '/dashboard/request-access'
 */
requestAccess.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: requestAccess.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\StaffRequestController::requestAccess
 * @see app/Http/Controllers/StaffRequestController.php:20
 * @route '/dashboard/request-access'
 */
    const requestAccessForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: requestAccess.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\StaffRequestController::requestAccess
 * @see app/Http/Controllers/StaffRequestController.php:20
 * @route '/dashboard/request-access'
 */
        requestAccessForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: requestAccess.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\StaffRequestController::requestAccess
 * @see app/Http/Controllers/StaffRequestController.php:20
 * @route '/dashboard/request-access'
 */
        requestAccessForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: requestAccess.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    requestAccess.form = requestAccessForm
/**
* @see \App\Http\Controllers\Dashboard\ProductController::products
 * @see app/Http/Controllers/Dashboard/ProductController.php:17
 * @route '/dashboard/products'
 */
export const products = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: products.url(options),
    method: 'get',
})

products.definition = {
    methods: ["get","head"],
    url: '/dashboard/products',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\ProductController::products
 * @see app/Http/Controllers/Dashboard/ProductController.php:17
 * @route '/dashboard/products'
 */
products.url = (options?: RouteQueryOptions) => {
    return products.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\ProductController::products
 * @see app/Http/Controllers/Dashboard/ProductController.php:17
 * @route '/dashboard/products'
 */
products.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: products.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\ProductController::products
 * @see app/Http/Controllers/Dashboard/ProductController.php:17
 * @route '/dashboard/products'
 */
products.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: products.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\ProductController::products
 * @see app/Http/Controllers/Dashboard/ProductController.php:17
 * @route '/dashboard/products'
 */
    const productsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: products.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\ProductController::products
 * @see app/Http/Controllers/Dashboard/ProductController.php:17
 * @route '/dashboard/products'
 */
        productsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: products.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\ProductController::products
 * @see app/Http/Controllers/Dashboard/ProductController.php:17
 * @route '/dashboard/products'
 */
        productsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: products.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    products.form = productsForm
/**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:346
 * @route '/dashboard/orders'
 */
export const orders = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: orders.url(options),
    method: 'get',
})

orders.definition = {
    methods: ["get","head"],
    url: '/dashboard/orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:346
 * @route '/dashboard/orders'
 */
orders.url = (options?: RouteQueryOptions) => {
    return orders.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:346
 * @route '/dashboard/orders'
 */
orders.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: orders.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:346
 * @route '/dashboard/orders'
 */
orders.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: orders.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:346
 * @route '/dashboard/orders'
 */
    const ordersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: orders.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:346
 * @route '/dashboard/orders'
 */
        ordersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: orders.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:346
 * @route '/dashboard/orders'
 */
        ordersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: orders.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    orders.form = ordersForm
/**
* @see \App\Http\Controllers\Dashboard\UserController::users
 * @see app/Http/Controllers/Dashboard/UserController.php:20
 * @route '/dashboard/users'
 */
export const users = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: users.url(options),
    method: 'get',
})

users.definition = {
    methods: ["get","head"],
    url: '/dashboard/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\UserController::users
 * @see app/Http/Controllers/Dashboard/UserController.php:20
 * @route '/dashboard/users'
 */
users.url = (options?: RouteQueryOptions) => {
    return users.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\UserController::users
 * @see app/Http/Controllers/Dashboard/UserController.php:20
 * @route '/dashboard/users'
 */
users.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: users.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\UserController::users
 * @see app/Http/Controllers/Dashboard/UserController.php:20
 * @route '/dashboard/users'
 */
users.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: users.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\UserController::users
 * @see app/Http/Controllers/Dashboard/UserController.php:20
 * @route '/dashboard/users'
 */
    const usersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: users.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\UserController::users
 * @see app/Http/Controllers/Dashboard/UserController.php:20
 * @route '/dashboard/users'
 */
        usersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: users.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\UserController::users
 * @see app/Http/Controllers/Dashboard/UserController.php:20
 * @route '/dashboard/users'
 */
        usersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: users.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    users.form = usersForm
/**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:588
 * @route '/dashboard/discounts'
 */
export const discounts = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: discounts.url(options),
    method: 'get',
})

discounts.definition = {
    methods: ["get","head"],
    url: '/dashboard/discounts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:588
 * @route '/dashboard/discounts'
 */
discounts.url = (options?: RouteQueryOptions) => {
    return discounts.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:588
 * @route '/dashboard/discounts'
 */
discounts.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: discounts.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:588
 * @route '/dashboard/discounts'
 */
discounts.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: discounts.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:588
 * @route '/dashboard/discounts'
 */
    const discountsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: discounts.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:588
 * @route '/dashboard/discounts'
 */
        discountsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: discounts.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:588
 * @route '/dashboard/discounts'
 */
        discountsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: discounts.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    discounts.form = discountsForm
/**
* @see \App\Http\Controllers\Dashboard\BannerController::banners
 * @see app/Http/Controllers/Dashboard/BannerController.php:18
 * @route '/dashboard/banners'
 */
export const banners = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: banners.url(options),
    method: 'get',
})

banners.definition = {
    methods: ["get","head"],
    url: '/dashboard/banners',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\BannerController::banners
 * @see app/Http/Controllers/Dashboard/BannerController.php:18
 * @route '/dashboard/banners'
 */
banners.url = (options?: RouteQueryOptions) => {
    return banners.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\BannerController::banners
 * @see app/Http/Controllers/Dashboard/BannerController.php:18
 * @route '/dashboard/banners'
 */
banners.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: banners.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\BannerController::banners
 * @see app/Http/Controllers/Dashboard/BannerController.php:18
 * @route '/dashboard/banners'
 */
banners.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: banners.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\BannerController::banners
 * @see app/Http/Controllers/Dashboard/BannerController.php:18
 * @route '/dashboard/banners'
 */
    const bannersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: banners.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\BannerController::banners
 * @see app/Http/Controllers/Dashboard/BannerController.php:18
 * @route '/dashboard/banners'
 */
        bannersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: banners.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\BannerController::banners
 * @see app/Http/Controllers/Dashboard/BannerController.php:18
 * @route '/dashboard/banners'
 */
        bannersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: banners.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    banners.form = bannersForm
/**
* @see \App\Http\Controllers\Dashboard\VideoController::videos
 * @see app/Http/Controllers/Dashboard/VideoController.php:19
 * @route '/dashboard/videos'
 */
export const videos = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: videos.url(options),
    method: 'get',
})

videos.definition = {
    methods: ["get","head"],
    url: '/dashboard/videos',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\VideoController::videos
 * @see app/Http/Controllers/Dashboard/VideoController.php:19
 * @route '/dashboard/videos'
 */
videos.url = (options?: RouteQueryOptions) => {
    return videos.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\VideoController::videos
 * @see app/Http/Controllers/Dashboard/VideoController.php:19
 * @route '/dashboard/videos'
 */
videos.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: videos.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\VideoController::videos
 * @see app/Http/Controllers/Dashboard/VideoController.php:19
 * @route '/dashboard/videos'
 */
videos.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: videos.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\VideoController::videos
 * @see app/Http/Controllers/Dashboard/VideoController.php:19
 * @route '/dashboard/videos'
 */
    const videosForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: videos.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\VideoController::videos
 * @see app/Http/Controllers/Dashboard/VideoController.php:19
 * @route '/dashboard/videos'
 */
        videosForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: videos.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\VideoController::videos
 * @see app/Http/Controllers/Dashboard/VideoController.php:19
 * @route '/dashboard/videos'
 */
        videosForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: videos.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    videos.form = videosForm
/**
* @see \App\Http\Controllers\Dashboard\ProfileController::profile
 * @see app/Http/Controllers/Dashboard/ProfileController.php:18
 * @route '/dashboard/profile'
 */
export const profile = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: profile.url(options),
    method: 'get',
})

profile.definition = {
    methods: ["get","head"],
    url: '/dashboard/profile',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::profile
 * @see app/Http/Controllers/Dashboard/ProfileController.php:18
 * @route '/dashboard/profile'
 */
profile.url = (options?: RouteQueryOptions) => {
    return profile.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::profile
 * @see app/Http/Controllers/Dashboard/ProfileController.php:18
 * @route '/dashboard/profile'
 */
profile.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: profile.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\ProfileController::profile
 * @see app/Http/Controllers/Dashboard/ProfileController.php:18
 * @route '/dashboard/profile'
 */
profile.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: profile.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\ProfileController::profile
 * @see app/Http/Controllers/Dashboard/ProfileController.php:18
 * @route '/dashboard/profile'
 */
    const profileForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: profile.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\ProfileController::profile
 * @see app/Http/Controllers/Dashboard/ProfileController.php:18
 * @route '/dashboard/profile'
 */
        profileForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: profile.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\ProfileController::profile
 * @see app/Http/Controllers/Dashboard/ProfileController.php:18
 * @route '/dashboard/profile'
 */
        profileForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: profile.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    profile.form = profileForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::notifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:79
 * @route '/dashboard/notifications'
 */
export const notifications = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: notifications.url(options),
    method: 'get',
})

notifications.definition = {
    methods: ["get","head"],
    url: '/dashboard/notifications',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::notifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:79
 * @route '/dashboard/notifications'
 */
notifications.url = (options?: RouteQueryOptions) => {
    return notifications.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::notifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:79
 * @route '/dashboard/notifications'
 */
notifications.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: notifications.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::notifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:79
 * @route '/dashboard/notifications'
 */
notifications.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: notifications.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::notifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:79
 * @route '/dashboard/notifications'
 */
    const notificationsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: notifications.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::notifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:79
 * @route '/dashboard/notifications'
 */
        notificationsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: notifications.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::notifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:79
 * @route '/dashboard/notifications'
 */
        notificationsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: notifications.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    notifications.form = notificationsForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::settings
 * @see app/Http/Controllers/Dashboard/SettingsController.php:18
 * @route '/dashboard/settings'
 */
export const settings = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(options),
    method: 'get',
})

settings.definition = {
    methods: ["get","head"],
    url: '/dashboard/settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::settings
 * @see app/Http/Controllers/Dashboard/SettingsController.php:18
 * @route '/dashboard/settings'
 */
settings.url = (options?: RouteQueryOptions) => {
    return settings.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::settings
 * @see app/Http/Controllers/Dashboard/SettingsController.php:18
 * @route '/dashboard/settings'
 */
settings.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::settings
 * @see app/Http/Controllers/Dashboard/SettingsController.php:18
 * @route '/dashboard/settings'
 */
settings.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: settings.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::settings
 * @see app/Http/Controllers/Dashboard/SettingsController.php:18
 * @route '/dashboard/settings'
 */
    const settingsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: settings.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::settings
 * @see app/Http/Controllers/Dashboard/SettingsController.php:18
 * @route '/dashboard/settings'
 */
        settingsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: settings.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::settings
 * @see app/Http/Controllers/Dashboard/SettingsController.php:18
 * @route '/dashboard/settings'
 */
        settingsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: settings.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    settings.form = settingsForm
/**
* @see \App\Http\Controllers\Dashboard\StaffController::staff
 * @see app/Http/Controllers/Dashboard/StaffController.php:42
 * @route '/dashboard/staff'
 */
export const staff = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: staff.url(options),
    method: 'get',
})

staff.definition = {
    methods: ["get","head"],
    url: '/dashboard/staff',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\StaffController::staff
 * @see app/Http/Controllers/Dashboard/StaffController.php:42
 * @route '/dashboard/staff'
 */
staff.url = (options?: RouteQueryOptions) => {
    return staff.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StaffController::staff
 * @see app/Http/Controllers/Dashboard/StaffController.php:42
 * @route '/dashboard/staff'
 */
staff.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: staff.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\StaffController::staff
 * @see app/Http/Controllers/Dashboard/StaffController.php:42
 * @route '/dashboard/staff'
 */
staff.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: staff.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::staff
 * @see app/Http/Controllers/Dashboard/StaffController.php:42
 * @route '/dashboard/staff'
 */
    const staffForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: staff.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StaffController::staff
 * @see app/Http/Controllers/Dashboard/StaffController.php:42
 * @route '/dashboard/staff'
 */
        staffForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: staff.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\StaffController::staff
 * @see app/Http/Controllers/Dashboard/StaffController.php:42
 * @route '/dashboard/staff'
 */
        staffForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: staff.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    staff.form = staffForm
/**
* @see \App\Http\Controllers\Dashboard\FeedbackController::feedback
 * @see app/Http/Controllers/Dashboard/FeedbackController.php:11
 * @route '/dashboard/feedback'
 */
export const feedback = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: feedback.url(options),
    method: 'get',
})

feedback.definition = {
    methods: ["get","head"],
    url: '/dashboard/feedback',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\FeedbackController::feedback
 * @see app/Http/Controllers/Dashboard/FeedbackController.php:11
 * @route '/dashboard/feedback'
 */
feedback.url = (options?: RouteQueryOptions) => {
    return feedback.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\FeedbackController::feedback
 * @see app/Http/Controllers/Dashboard/FeedbackController.php:11
 * @route '/dashboard/feedback'
 */
feedback.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: feedback.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\FeedbackController::feedback
 * @see app/Http/Controllers/Dashboard/FeedbackController.php:11
 * @route '/dashboard/feedback'
 */
feedback.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: feedback.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\FeedbackController::feedback
 * @see app/Http/Controllers/Dashboard/FeedbackController.php:11
 * @route '/dashboard/feedback'
 */
    const feedbackForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: feedback.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\FeedbackController::feedback
 * @see app/Http/Controllers/Dashboard/FeedbackController.php:11
 * @route '/dashboard/feedback'
 */
        feedbackForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: feedback.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\FeedbackController::feedback
 * @see app/Http/Controllers/Dashboard/FeedbackController.php:11
 * @route '/dashboard/feedback'
 */
        feedbackForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: feedback.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    feedback.form = feedbackForm
/**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:57
 * @route '/dashboard/report'
 */
export const report = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: report.url(options),
    method: 'get',
})

report.definition = {
    methods: ["get","head"],
    url: '/dashboard/report',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:57
 * @route '/dashboard/report'
 */
report.url = (options?: RouteQueryOptions) => {
    return report.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:57
 * @route '/dashboard/report'
 */
report.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: report.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:57
 * @route '/dashboard/report'
 */
report.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: report.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:57
 * @route '/dashboard/report'
 */
    const reportForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: report.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:57
 * @route '/dashboard/report'
 */
        reportForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: report.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:57
 * @route '/dashboard/report'
 */
        reportForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: report.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    report.form = reportForm
/**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:809
 * @route '/dashboard/stats'
 */
export const stats = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})

stats.definition = {
    methods: ["get","head"],
    url: '/dashboard/stats',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:809
 * @route '/dashboard/stats'
 */
stats.url = (options?: RouteQueryOptions) => {
    return stats.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:809
 * @route '/dashboard/stats'
 */
stats.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:809
 * @route '/dashboard/stats'
 */
stats.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: stats.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:809
 * @route '/dashboard/stats'
 */
    const statsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: stats.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:809
 * @route '/dashboard/stats'
 */
        statsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: stats.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:809
 * @route '/dashboard/stats'
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
const dashboard = {
    login: Object.assign(login, loginDf2c2a),
register: Object.assign(register, register702019),
index: Object.assign(index, index),
export: Object.assign(exportMethod, exportMethod),
logout: Object.assign(logout, logout),
requestAccess: Object.assign(requestAccess, requestAccessD72a72),
products: Object.assign(products, products237d17),
orders: Object.assign(orders, ordersB47e5f),
users: Object.assign(users, users48860f),
discounts: Object.assign(discounts, discounts5ba67e),
banners: Object.assign(banners, bannersEe4e07),
videos: Object.assign(videos, videos3b3162),
profile: Object.assign(profile, profile937a89),
notifications: Object.assign(notifications, notifications),
settings: Object.assign(settings, settings69f00b),
deliveryProviders: Object.assign(deliveryProviders, deliveryProviders),
staff: Object.assign(staff, staffC58c8e),
admin: Object.assign(admin, admin),
staffRequests: Object.assign(staffRequests, staffRequests),
telegram: Object.assign(telegram, telegram),
feedback: Object.assign(feedback, feedback),
report: Object.assign(report, report),
stats: Object.assign(stats, stats),
}

export default dashboard