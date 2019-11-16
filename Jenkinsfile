pipeline {
    agent any

    stages {
        stage ("Checkout") {
            steps{
                checkout scm
                sh 'docker-compose up -d'
            }
        }
    }
} 
