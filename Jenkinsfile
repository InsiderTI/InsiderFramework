pipeline {
    agent {
        dockerfile true
    }
    stages {
        stage ("Checkout") {
            steps{
                sh "sudo mkdir -p /var/www/"
                sh "sudo chmod 777 -R /var/www/"
                dir('/var/www/insiderframework-site'){
                    checkout scm
                }
            }
        }

        stage ("Build") {
            steps {
                // Getting the commit id to be used as a tag (version) of the image
                // sh "git rev-parse --short HEAD > commit-id"
                // def tag = readFile('commit-id').replace("\n", "").replace("\r", "")
                
                // Configures the app name, the repository url and the image name with the version
                // def appName = "app"
                // def registryHost = "127.0.0.1:30400/"
                // def imageName = "${registryHost}${appName}:${tag} docker/insider_framework-site.dockerfile"
                    
                dir('/var/www/insiderframework-site/docker'){
                    sh "sudo docker-compose build"
                }
            }
        }
        stage ("Deploy"){
            steps {
                // Simple deployment
                dir('/var/www/insiderframework-site/docker'){
                    sh "sudo docker-compose up -d"
                }
            
                // Deploy with Kubernetes
                // input "Deploy to PROD"
                // sh "kubectl apply -f https://raw.githubusercontent.com/InsiderTI/InsiderFramework-site/master/k8s_app.yaml"
                // sh "kubectl set image deployment app app=${imageName} --record"
                // sh "kubectl rollout status deployment/app"
            }
        }
    }
} 
