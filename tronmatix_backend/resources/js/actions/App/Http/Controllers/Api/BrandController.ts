import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\BrandController::index
 * @see app/Http/Controllers/Api/BrandController.php:41
 * @route '/api/brands'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/brands',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\BrandController::index
 * @see app/Http/Controllers/Api/BrandController.php:41
 * @route '/api/brands'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BrandController::index
 * @see app/Http/Controllers/Api/BrandController.php:41
 * @route '/api/brands'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\BrandController::index
 * @see app/Http/Controllers/Api/BrandController.php:41
 * @route '/api/brands'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\BrandController::index
 * @see app/Http/Controllers/Api/BrandController.php:41
 * @route '/api/brands'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\BrandController::index
 * @see app/Http/Controllers/Api/BrandController.php:41
 * @route '/api/brands'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\BrandController::index
 * @see app/Http/Controllers/Api/BrandController.php:41
 * @route '/api/brands'
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
* @see \App\Http\Controllers\Api\BrandController::productBrands
 * @see app/Http/Controllers/Api/BrandController.php:33
 * @route '/api/brands/product-list'
 */
export const productBrands = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: productBrands.url(options),
    method: 'get',
})

productBrands.definition = {
    methods: ["get","head"],
    url: '/api/brands/product-list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\BrandController::productBrands
 * @see app/Http/Controllers/Api/BrandController.php:33
 * @route '/api/brands/product-list'
 */
productBrands.url = (options?: RouteQueryOptions) => {
    return productBrands.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BrandController::productBrands
 * @see app/Http/Controllers/Api/BrandController.php:33
 * @route '/api/brands/product-list'
 */
productBrands.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: productBrands.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\BrandController::productBrands
 * @see app/Http/Controllers/Api/BrandController.php:33
 * @route '/api/brands/product-list'
 */
productBrands.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: productBrands.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\BrandController::productBrands
 * @see app/Http/Controllers/Api/BrandController.php:33
 * @route '/api/brands/product-list'
 */
    const productBrandsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: productBrands.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\BrandController::productBrands
 * @see app/Http/Controllers/Api/BrandController.php:33
 * @route '/api/brands/product-list'
 */
        productBrandsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: productBrands.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\BrandController::productBrands
 * @see app/Http/Controllers/Api/BrandController.php:33
 * @route '/api/brands/product-list'
 */
        productBrandsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: productBrands.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    productBrands.form = productBrandsForm
const BrandController = { index, productBrands }

export default BrandController