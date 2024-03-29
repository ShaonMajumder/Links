import React from 'react';
import { Redirect } from 'react-router-dom';
import apiClient,{login_url,csrf_token_url } from '../services/api';
import Cookies from 'js-cookie';
import { useDispatch, useSelector } from 'react-redux';
import { setLoggedIn, setLoggedOut} from '../reducers/linkSlice';
import Swal from 'sweetalert2';

const Login = (props) => {
    const dispatch = useDispatch();
    const isLoggedIn = useSelector((store) => store.links.isLoggedIn);
    // console.log("If is logged in",isLoggedIn)



    const [email, setEmail] = React.useState('');
    const [password, setPassword] = React.useState('');
    const [toHome, setToHome] = React.useState(false);
    const [authError, setAuthError] = React.useState(false);
    const [unknownError, setUnknownError] = React.useState(false);
    const handleSubmit = (e) => {
        e.preventDefault();
        setAuthError(false);
        setUnknownError(false);
        apiClient.get(csrf_token_url)
        .then(response => {
            apiClient.post(login_url, {
                email: email,
                password: password
            }).then(response => {
                if (response.status === 200) { //204
                    Cookies.set('access_token',response.data.access_token)
                    props.login();
                    setToHome(true);
                    dispatch(setLoggedIn())
                }
            }).catch(error => {
                if (error.response && error.response.status === 422) {
                    setAuthError(true);
                } else {
                    setUnknownError(true);
                    console.error(error);
                }
            });
        }).catch(error => {
            Swal.fire({
                text:error.message,
                icon:"error"
            })
        });
    }
    if (toHome === true) {
        return <Redirect to='/' />
    }
    return (
        <div>
            <h3>Login</h3>
            <form onSubmit={handleSubmit}>
                <div className="form-group">
                    <input
                        type="email"
                        name="email"
                        className={"form-control" + (authError || unknownError ? ' is-invalid' : '')}
                        placeholder="Email"
                        value={email}
                        onChange={e => setEmail(e.target.value)}
                        required
                    />
                </div>
                <div className="form-group">
                    <input
                        type="password"
                        name="password"
                        className={"form-control" + (authError || unknownError ? ' is-invalid' : '')}
                        placeholder="Password"
                        value={password}
                        onChange={e => setPassword(e.target.value)}
                        required
                    />
                </div>
                {authError ? <div className="alert alert-danger">Credentials not recognised. Please try again.</div> : null}
                {unknownError ? <div className="alert alert-danger">There was an error submitting your details.</div> : null}
                <button type="submit" className="btn btn-primary">Login</button>  
            </form>
        </div>
    );
};

export default Login;