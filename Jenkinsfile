pipeline {
    agent any

    environment {
        DOCKERHUB_USER = 'chaimaeazzouz'
        IMAGE_NAME     = "${DOCKERHUB_USER}/projet-php"
        IMAGE_TAG      = "${BRANCH_NAME}-${BUILD_NUMBER}"
    }

    stages {

        stage('GitLeaks : détection de secrets') {
            steps {
                sh '''
                    docker run --rm --volumes-from jenkins -w "$WORKSPACE" \
                      zricethezav/gitleaks:latest detect \
                      --source . --no-banner --verbose --exit-code 1
                '''
            }
        }

        stage('SonarQube : analyse du code') {
            steps {
                script {
                    def scannerHome = tool 'SonarScanner'
                    withSonarQubeEnv('SonarQube') {
                        sh """
                            ${scannerHome}/bin/sonar-scanner \
                              -Dsonar.projectKey=projet-php \
                              -Dsonar.projectName=projet-php \
                              -Dsonar.sources=src
                        """
                    }
                }
            }
        }

        stage('Quality Gate') {
            steps {
                timeout(time: 5, unit: 'MINUTES') {
                    waitForQualityGate abortPipeline: true
                }
            }
        }

        stage('Docker : build') {
            steps {
                sh 'docker build -t $IMAGE_NAME:$IMAGE_TAG .'
            }
        }

        stage('Trivy : scan de l\'image') {
            steps {
                sh '''
                    docker run --rm \
                      -v /var/run/docker.sock:/var/run/docker.sock \
                      -v trivy_cache:/root/.cache/ \
                      aquasec/trivy:latest image \
                      --severity CRITICAL --ignore-unfixed \
                      --exit-code 1 --no-progress \
                      $IMAGE_NAME:$IMAGE_TAG
                '''
            }
        }

        stage('Docker Hub : publication') {
            steps {
                withCredentials([usernamePassword(
                    credentialsId: 'dockerhub-credentials',
                    usernameVariable: 'DH_USER',
                    passwordVariable: 'DH_TOKEN')]) {
                    sh '''
                        echo "$DH_TOKEN" | docker login -u "$DH_USER" --password-stdin
                        docker push $IMAGE_NAME:$IMAGE_TAG
                        docker logout
                    '''
                }
            }
        }
    }

    post {
        success { echo "Pipeline réussi : image $IMAGE_NAME:$IMAGE_TAG publiée." }
        failure { echo "Pipeline en échec : consulte l'étape en rouge." }
    }
}