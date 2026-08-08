import AuthController from './AuthController'
import Api from './Api'
import Auth from './Auth'
import Dashboard from './Dashboard'
import AdminAuthController from './AdminAuthController'
import DashboardController from './DashboardController'
import StaffRequestController from './StaffRequestController'
const Controllers = {
    AuthController: Object.assign(AuthController, AuthController),
Api: Object.assign(Api, Api),
Auth: Object.assign(Auth, Auth),
Dashboard: Object.assign(Dashboard, Dashboard),
AdminAuthController: Object.assign(AdminAuthController, AdminAuthController),
DashboardController: Object.assign(DashboardController, DashboardController),
StaffRequestController: Object.assign(StaffRequestController, StaffRequestController),
}

export default Controllers