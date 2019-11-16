pipeline {
    agent {
        dockerfile true
    }
    stages {
        stage ("Checkout") {
            steps{
                sh "mkdir -p /var/www/"
                // sh "chmod 777 -R /var/www/"
                dir('/var/www/insiderframework-site'){
                    checkout scm
                    sh "docker-compose build"
                    sh "docker-compose up -d"
                }
            }
        }
    }
} 
