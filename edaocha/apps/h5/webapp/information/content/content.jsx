import React, {Component} from 'react';

class InformationContent extends Component
{
  render() {
    return(
      <div style={styles.contentBox}>
          <div id="informationMain" dangerouslySetInnerHTML={{__html: this.props.content}} />
      </div>
    );
  }
}

const styles = {
  contentBox: {
    backgroundColor: '#fff',
    marginTop: 10,
  },
}
export default InformationContent;
