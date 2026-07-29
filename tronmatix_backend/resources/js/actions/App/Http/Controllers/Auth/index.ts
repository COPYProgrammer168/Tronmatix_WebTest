import StaffAuthController from './StaffAuthController'
import DevAuthController from './DevAuthController'
const Auth = {
    StaffAuthController: Object.assign(StaffAuthController, StaffAuthController),
DevAuthController: Object.assign(DevAuthController, DevAuthController),
}

export default Auth