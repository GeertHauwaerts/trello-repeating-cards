ARG VARIANT="20.04"
FROM mcr.microsoft.com/vscode/devcontainers/base:0-ubuntu${VARIANT}

RUN apt-get update \
  && export DEBIAN_FRONTEND=noninteractive \
  && apt-get -y install --no-install-recommends composer git php-curl php-zip php-xml unzip zip

RUN git clone --depth=1 https://github.com/romkatv/powerlevel10k.git ${ZSH_CUSTOM:-~/.oh-my-zsh/custom}/themes/powerlevel10k
RUN su vscode -c "git clone --depth=1 https://github.com/romkatv/powerlevel10k.git ${ZSH_CUSTOM:-~/.oh-my-zsh/custom}/themes/powerlevel10k"

COPY p10k.zsh /root/.p10k.zsh
COPY p10k.zsh /home/vscode/.p10k.zsh

RUN echo '[[ ! -f ~/.p10k.zsh ]] || source ~/.p10k.zsh' >> ~/.zshrc
RUN su vscode -c "echo '[[ ! -f ~/.p10k.zsh ]] || source ~/.p10k.zsh' >> ~/.zshrc"

RUN sed -i 's#robbyrussell#powerlevel10k/powerlevel10k#' ~/.zshrc
RUN su vscode -c "sed -i 's#robbyrussell#powerlevel10k/powerlevel10k#' ~/.zshrc"

RUN chsh -s /bin/zsh root
RUN chsh -s /bin/zsh vscode
